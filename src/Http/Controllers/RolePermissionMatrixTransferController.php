<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\Menu;
use Aurix\Models\Role;

class RolePermissionMatrixTransferController extends Controller
{
    public function export(Request $request, Role $role)
    {
        $format = strtolower((string) $request->query('format', 'json'));
        $matrix = $this->buildMatrix($role);

        if ($format === 'csv') {
            $csv = $this->toCsv($matrix);

            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="role-' . $role->id . '-permissions-matrix.csv"',
            ]);
        }

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
            ],
            'matrix' => $matrix,
        ]);
    }

    public function import(Request $request, Role $role): JsonResponse
    {
        if ($this->isFullAccessRole($role)) {
            return response()->json([
                'message' => 'This role has full access by default and cannot be edited here.',
            ], 422);
        }

        $format = strtolower((string) $request->input('format', 'json'));

        if ($format === 'csv') {
            $validator = Validator::make($request->all(), [
                'csv' => ['required', 'string'],
            ]);

            $payload = $validator->validate();
            $items = $this->parseCsv((string) $payload['csv']);
        } else {
            $validator = Validator::make($request->all(), [
                'items' => ['required', 'array', 'min:1'],
                'items.*.menu_id' => ['nullable', 'integer', 'exists:' . config('aurix.tables.menus', 'menus') . ',id'],
                'items.*.menu_slug' => ['nullable', 'string'],
                'items.*.create' => ['nullable', 'boolean'],
                'items.*.update' => ['nullable', 'boolean'],
                'items.*.delete' => ['nullable', 'boolean'],
                'items.*.edit' => ['nullable', 'boolean'],
            ]);

            $payload = $validator->validate();
            /** @var array<int, array<string, mixed>> $items */
            $items = collect($payload['items'])
                ->map(static fn (array $row): array => [
                    'menu_id' => $row['menu_id'] ?? null,
                    'menu_slug' => $row['menu_slug'] ?? null,
                    'create' => (bool) ($row['create'] ?? false),
                    'update' => (bool) ($row['update'] ?? false),
                    'delete' => (bool) ($row['delete'] ?? false),
                    'edit' => (bool) ($row['edit'] ?? false),
                ])
                ->all();
        }

        if ($this->containsSystemMenuAssignments($items)) {
            return response()->json([
                'message' => 'RBAC/system menus cannot be assigned to non-admin roles.',
            ], 422);
        }

        $role->syncMenuPermissions($items);

        return response()->json([
            'message' => 'Role permission matrix imported successfully.',
            'count' => count($items),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMatrix(Role $role): array
    {
        $menus = Menu::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $assigned = $role->menuPermissions()->get()->keyBy('menu_id');

        return $menus->map(function (Menu $menu) use ($assigned): array {
            $pivot = $assigned->get($menu->id);

            return [
                'menu_id' => $menu->id,
                'menu_name' => $menu->name,
                'menu_slug' => $menu->slug,
                'route' => $menu->route,
                'actions' => [
                    'create' => (bool) ($pivot?->can_create ?? false),
                    'update' => (bool) ($pivot?->can_update ?? false),
                    'delete' => (bool) ($pivot?->can_delete ?? false),
                    'edit' => (bool) ($pivot?->can_edit ?? false),
                ],
            ];
        })->values()->all();
    }

    /**
     * @param array<int, array<string, mixed>> $matrix
     */
    private function toCsv(array $matrix): string
    {
        $rows = [
            ['menu_id', 'menu_slug', 'menu_name', 'route', 'create', 'update', 'delete', 'edit'],
        ];

        foreach ($matrix as $item) {
            $rows[] = [
                (string) $item['menu_id'],
                (string) $item['menu_slug'],
                (string) $item['menu_name'],
                (string) ($item['route'] ?? ''),
                ((bool) $item['actions']['create']) ? '1' : '0',
                ((bool) $item['actions']['update']) ? '1' : '0',
                ((bool) $item['actions']['delete']) ? '1' : '0',
                ((bool) $item['actions']['edit']) ? '1' : '0',
            ];
        }

        $out = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv === false ? '' : $csv;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseCsv(string $csv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];
        if (count($lines) <= 1) {
            return [];
        }

        $header = str_getcsv((string) array_shift($lines));
        $headerMap = array_flip($header);

        $items = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);
            $menuId = $this->csvCell($row, $headerMap, 'menu_id');
            $menuSlug = $this->csvCell($row, $headerMap, 'menu_slug');

            $items[] = [
                'menu_id' => $menuId !== '' ? (int) $menuId : null,
                'menu_slug' => $menuSlug !== '' ? $menuSlug : null,
                'create' => $this->toBool($this->csvCell($row, $headerMap, 'create')),
                'update' => $this->toBool($this->csvCell($row, $headerMap, 'update')),
                'delete' => $this->toBool($this->csvCell($row, $headerMap, 'delete')),
                'edit' => $this->toBool($this->csvCell($row, $headerMap, 'edit')),
            ];
        }

        return $items;
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     */
    private function csvCell(array $row, array $headerMap, string $column): string
    {
        if (! isset($headerMap[$column])) {
            return '';
        }

        return (string) ($row[$headerMap[$column]] ?? '');
    }

    private function toBool(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'y'], true);
    }

    private function isFullAccessRole(Role $role): bool
    {
        $fullAccessRoles = collect(array_merge(
            ['admin', 'super-admin'],
            (array) config('aurix.rbac.super_admin_roles', [])
        ))
            ->map(static fn ($slug): string => strtolower((string) $slug))
            ->unique()
            ->values()
            ->all();

        return in_array(strtolower((string) $role->slug), $fullAccessRoles, true);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function containsSystemMenuAssignments(array $items): bool
    {
        $menuIds = collect($items)
            ->pluck('menu_id')
            ->filter(static fn ($id): bool => $id !== null)
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
        $menuSlugs = collect($items)
            ->pluck('menu_slug')
            ->filter(static fn ($slug): bool => is_string($slug) && trim($slug) !== '')
            ->map(static fn ($slug): string => trim((string) $slug))
            ->unique()
            ->values()
            ->all();

        if ($menuIds === [] && $menuSlugs === []) {
            return false;
        }

        $uiPath = '/' . trim((string) config('aurix.ui.path', 'auth/rbac'), '/');
        $menus = Menu::query()
            ->where(function ($q) use ($menuIds, $menuSlugs): void {
                if ($menuIds !== []) {
                    $q->whereIn('id', $menuIds);
                }
                if ($menuSlugs !== []) {
                    $method = $menuIds !== [] ? 'orWhereIn' : 'whereIn';
                    $q->{$method}('slug', $menuSlugs);
                }
            })
            ->get(['id', 'slug', 'route']);
        $menusById = $menus->keyBy('id');
        $menusBySlug = $menus->keyBy('slug');

        foreach ($items as $item) {
            $menu = null;
            $menuId = isset($item['menu_id']) && $item['menu_id'] !== null ? (int) $item['menu_id'] : null;
            if ($menuId !== null) {
                $menu = $menusById->get($menuId);
            }
            if ($menu === null && isset($item['menu_slug']) && is_string($item['menu_slug'])) {
                $menu = $menusBySlug->get(trim((string) $item['menu_slug']));
            }

            if ($menu === null || ! $this->isSystemMenu($menu->route, $uiPath)) {
                continue;
            }

            if ((bool) ($item['create'] ?? false) || (bool) ($item['update'] ?? false) || (bool) ($item['delete'] ?? false) || (bool) ($item['edit'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    private function isSystemMenu(?string $route, string $uiPath): bool
    {
        $value = trim((string) ($route ?? ''));
        if ($value === '') {
            return false;
        }

        if (! str_starts_with($value, '/')) {
            $value = '/' . ltrim($value, '/');
        }

        return $value === $uiPath || str_starts_with($value, $uiPath . '/');
    }
}
