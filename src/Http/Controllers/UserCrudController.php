<?php

declare(strict_types=1);

namespace Aurix\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Aurix\Services\MenuAccessService;

class UserCrudController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userModel = $this->userModel();
        $defaultPerPage = (int) config('aurix.pagination.default_per_page', 15);
        $maxPerPage = (int) config('aurix.pagination.max_per_page', 100);
        $perPage = max(1, min((int) $request->query('per_page', $defaultPerPage), $maxPerPage));
        $search = trim((string) $request->query('q', ''));

        $query = $userModel::query()->select(['id', 'name', 'email', 'created_at', 'updated_at']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('name')->paginate($perPage);
        $userIds = collect($users->items())->pluck('id')->all();

        $rolesByUser = $this->rolesByUserIds($userIds);

        $data = collect($users->items())->map(function ($user) use ($rolesByUser): array {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $rolesByUser[$user->id] ?? [],
            ];
        })->values()->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(int $user): JsonResponse
    {
        $modelClass = $this->userModel();
        $record = $modelClass::query()->findOrFail($user);

        return response()->json([
            'data' => [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
                'roles' => $this->rolesByUserIds([$record->id])[$record->id] ?? [],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $usersTable = config('aurix.tables.users', 'users');

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . $usersTable . ',email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'max:255'],
        ]);

        $payload = $validator->validate();

        $modelClass = $this->userModel();
        /** @var Model $user */
        $user = $modelClass::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make((string) $payload['password']),
        ]);

        $this->syncUserRoles((int) $user->id, (array) ($payload['roles'] ?? []));

        return $this->show((int) $user->id)->setStatusCode(201);
    }

    public function update(Request $request, int $user): JsonResponse
    {
        $usersTable = config('aurix.tables.users', 'users');

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:' . $usersTable . ',email,' . $user],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'max:255'],
        ]);

        $payload = $validator->validate();

        $modelClass = $this->userModel();
        /** @var Model $record */
        $record = $modelClass::query()->findOrFail($user);

        if (array_key_exists('name', $payload)) {
            $record->setAttribute('name', $payload['name']);
        }
        if (array_key_exists('email', $payload)) {
            $record->setAttribute('email', $payload['email']);
        }
        if (! empty($payload['password'])) {
            $record->setAttribute('password', Hash::make((string) $payload['password']));
        }

        $record->save();

        if (array_key_exists('roles', $payload)) {
            $this->syncUserRoles((int) $record->id, (array) $payload['roles']);
        }

        return $this->show((int) $record->id);
    }

    public function destroy(Request $request, int $user): JsonResponse
    {
        if ((int) optional($request->user())->getAuthIdentifier() === $user) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $modelClass = $this->userModel();
        $record = $modelClass::query()->findOrFail($user);
        $record->delete();

        $this->clearRbacCache($user);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    private function userModel(): string
    {
        /** @var class-string<Model> $model */
        $model = (string) config('auth.providers.users.model');

        return $model;
    }

    /**
     * @param array<int> $userIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function rolesByUserIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $tables = config('aurix.tables');

        $rows = DB::connection(config('aurix.database.connection'))
            ->table($tables['user_roles'])
            ->join($tables['roles'], $tables['roles'] . '.id', '=', $tables['user_roles'] . '.role_id')
            ->whereIn($tables['user_roles'] . '.user_id', $userIds)
            ->get([
                $tables['user_roles'] . '.user_id as user_id',
                $tables['roles'] . '.id as id',
                $tables['roles'] . '.name as name',
                $tables['roles'] . '.slug as slug',
            ]);

        $grouped = [];
        foreach ($rows as $row) {
            $uid = (int) $row->user_id;
            $grouped[$uid] ??= [];
            $grouped[$uid][] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
            ];
        }

        return $grouped;
    }

    /**
     * @param array<int, string> $roles
     */
    private function syncUserRoles(int $userId, array $roles): void
    {
        $tables = config('aurix.tables');

        $roleIds = DB::connection(config('aurix.database.connection'))
            ->table($tables['roles'])
            ->whereIn('slug', $roles)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        DB::connection(config('aurix.database.connection'))
            ->table($tables['user_roles'])
            ->where('user_id', $userId)
            ->delete();

        $now = now();
        foreach ($roleIds as $roleId) {
            DB::connection(config('aurix.database.connection'))
                ->table($tables['user_roles'])
                ->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
        }

        $this->clearRbacCache($userId);
    }

    private function clearRbacCache(int $userId): void
    {
        cache()->forget('aurix:' . $userId . ':roles');
        cache()->forget('aurix:' . $userId . ':permissions');
        cache()->forget('aurix:menu_access:' . $userId);
        app(MenuAccessService::class)->bumpCacheVersion();
    }
}
