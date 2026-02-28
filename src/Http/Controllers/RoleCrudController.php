<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Aurix\Models\Role;
use Aurix\Support\Concerns\InteractsWithAurixValidation;

class RoleCrudController extends Controller
{
    use InteractsWithAurixValidation;
    public function index(Request $request): JsonResponse
    {
        $defaultPerPage = (int) config('aurix.pagination.default_per_page', 15);
        $maxPerPage = (int) config('aurix.pagination.max_per_page', 100);
        $perPage = max(1, min((int) $request->query('per_page', $defaultPerPage), $maxPerPage));
        $search = trim((string) $request->query('q', ''));
        $sortBy = (string) $request->query('sort_by', 'name');
        $sortDir = strtolower((string) $request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortable = ['name', 'slug', 'created_at', 'updated_at'];

        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = 'name';
        }

        $query = Role::query()
            ->select(['id', 'name', 'slug', 'description', 'created_at', 'updated_at']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $roles = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return response()->json([
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(['data' => $role]);
    }

    public function store(Request $request): JsonResponse
    {
        $rolesTable = config('aurix.tables.roles', 'roles');

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $this->uniqueRule($rolesTable, 'slug')],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = $validator->validate();

        $role = Role::query()->create($payload);

        return response()->json(['data' => $role], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $rolesTable = config('aurix.tables.roles', 'roles');

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', $this->uniqueRule($rolesTable, 'slug', $role->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = $validator->validate();
        $role->fill($payload)->save();

        return response()->json(['data' => $role->fresh()]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
