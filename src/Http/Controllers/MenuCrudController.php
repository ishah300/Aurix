<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\Menu;
use Aurix\Services\MenuAccessService;
use Aurix\Support\Concerns\InteractsWithAurixValidation;

class MenuCrudController extends Controller
{
    use InteractsWithAurixValidation;
    public function index(Request $request): JsonResponse
    {
        $defaultPerPage = (int) config('aurix.pagination.default_per_page', 15);
        $maxPerPage = (int) config('aurix.pagination.max_per_page', 100);
        $perPage = max(1, min((int) $request->query('per_page', $defaultPerPage), $maxPerPage));
        $search = trim((string) $request->query('q', ''));
        $sortBy = (string) $request->query('sort_by', 'sort_order');
        $sortDir = strtolower((string) $request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = ['name', 'slug', 'sort_order', 'created_at', 'updated_at'];

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'sort_order';
        }

        $query = Menu::query()
            ->select(['id', 'name', 'slug', 'route', 'icon', 'sort_order', 'parent_id', 'is_active', 'created_at', 'updated_at']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('route', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('active')) {
            $query->where('is_active', filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int) $request->query('parent_id'));
        }

        $menus = $query->orderBy($sortBy, $sortDir)->orderBy('id')->paginate($perPage);

        return response()->json([
            'data' => $menus->items(),
            'meta' => [
                'current_page' => $menus->currentPage(),
                'last_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ],
        ]);
    }

    public function show(Menu $menu): JsonResponse
    {
        return response()->json(['data' => $menu]);
    }

    public function store(Request $request): JsonResponse
    {
        $input = $this->normalizePayload($request->all());
        $menusTable = config('aurix.tables.menus', 'menus');
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $this->uniqueRule($menusTable, 'slug')],
            'route' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'integer', $this->existsRule($menusTable, 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = $validator->validate();
        $payload['sort_order'] = (int) ($payload['sort_order'] ?? 0);
        $payload['is_active'] = (bool) ($payload['is_active'] ?? true);

        $menu = Menu::query()->create($payload);
        app(MenuAccessService::class)->bumpCacheVersion();

        return response()->json(['data' => $menu], 201);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $input = $this->normalizePayload($request->all());
        $menusTable = config('aurix.tables.menus', 'menus');
        $validator = Validator::make($input, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', $this->uniqueRule($menusTable, 'slug', $menu->id)],
            'route' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'integer', $this->existsRule($menusTable, 'id')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = $validator->validate();
        $menu->fill($payload)->save();
        app(MenuAccessService::class)->bumpCacheVersion();

        return response()->json(['data' => $menu->fresh()]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $tables = config('aurix.tables');
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:' . $tables['menus'] . ',id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:' . $tables['menus'] . ',id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $payload = $validator->validate();
        $items = collect($payload['items']);
        $ids = $items->pluck('id')->map(fn ($v): int => (int) $v)->all();
        $menusById = Menu::query()->whereIn('id', $ids)->get()->keyBy('id');

        foreach ($items as $item) {
            $id = (int) $item['id'];
            $parentId = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;

            if ($parentId === $id) {
                return response()->json(['message' => 'A menu cannot be its own parent.'], 422);
            }

            if ($parentId !== null && $this->wouldCreateCycle($id, $parentId, $items)) {
                return response()->json(['message' => 'Invalid hierarchy: cycle detected.'], 422);
            }
        }

        DB::connection(config('aurix.database.connection'))->transaction(function () use ($items, $menusById): void {
            foreach ($items as $item) {
                $menu = $menusById[(int) $item['id']] ?? null;
                if ($menu === null) {
                    continue;
                }

                $menu->parent_id = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;
                $menu->sort_order = (int) $item['sort_order'];
                $menu->save();
            }
        });
        app(MenuAccessService::class)->bumpCacheVersion();

        return response()->json(['message' => 'Menu order updated successfully.']);
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();
        app(MenuAccessService::class)->bumpCacheVersion();

        return response()->json(['message' => 'Menu deleted successfully.']);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalizePayload(array $input): array
    {
        if (array_key_exists('menu_title', $input) && ! array_key_exists('name', $input)) {
            $input['name'] = $input['menu_title'];
        }

        if (array_key_exists('menu_slug', $input) && ! array_key_exists('slug', $input)) {
            $input['slug'] = $input['menu_slug'];
        }

        if (array_key_exists('menu_sort_order', $input) && ! array_key_exists('sort_order', $input)) {
            $input['sort_order'] = $input['menu_sort_order'];
        }

        if (array_key_exists('menu_parent_id', $input) && ! array_key_exists('parent_id', $input)) {
            $input['parent_id'] = $input['menu_parent_id'];
        }

        return $input;
    }

    /**
     * @param \Illuminate\Support\Collection<int, array{id:int,parent_id:int|null,sort_order:int}> $items
     */
    private function wouldCreateCycle(int $id, int $parentId, \Illuminate\Support\Collection $items): bool
    {
        $parentMap = $items
            ->mapWithKeys(fn ($item): array => [
                (int) $item['id'] => $item['parent_id'] !== null ? (int) $item['parent_id'] : null,
            ])
            ->all();

        $seen = [];
        $cursor = $parentId;

        while ($cursor !== null) {
            if ($cursor === $id) {
                return true;
            }

            if (isset($seen[$cursor])) {
                return true;
            }

            $seen[$cursor] = true;
            $cursor = $parentMap[$cursor] ?? null;
        }

        return false;
    }
}
