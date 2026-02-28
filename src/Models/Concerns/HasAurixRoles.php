<?php

declare(strict_types=1);

namespace Aurix\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Aurix\Contracts\PermissionResolver;
use Aurix\Models\Role;

trait HasAurixRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, config('aurix.tables.user_roles', 'user_roles'));
    }

    public function assignRole(Role|string $role): void
    {
        $roleId = $role instanceof Role
            ? $role->getKey()
            : Role::query()->where('slug', $role)->value('id');

        if ($roleId !== null) {
            $this->roles()->syncWithoutDetaching([$roleId]);
            $this->clearAurixRbacCache();
        }
    }

    public function hasRole(string $role): bool
    {
        /** @var PermissionResolver $resolver */
        $resolver = app(PermissionResolver::class);

        return $resolver->hasRole($this, $role);
    }

    public function hasPermissionTo(string $permission, array $arguments = []): bool
    {
        /** @var PermissionResolver $resolver */
        $resolver = app(PermissionResolver::class);

        return $resolver->hasPermission($this, $permission, $arguments);
    }

    protected function clearAurixRbacCache(): void
    {
        $id = $this->getAuthIdentifier();

        if ($id === null) {
            return;
        }

        Cache::forget('aurix:' . $id . ':roles');
        Cache::forget('aurix:' . $id . ':permissions');
        Cache::forget('aurix:menu_access:' . $id);
    }
}
