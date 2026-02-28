<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Aurix\Services\MenuAccessService;
use Aurix\Support\RoleMenuPermissionSyncer;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.roles', 'roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('aurix.tables.role_permissions', 'role_permissions')
        );
    }

    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model');

        return $this->belongsToMany($userModel, config('aurix.tables.user_roles', 'user_roles'));
    }

    public function menuPermissions(): HasMany
    {
        return $this->hasMany(
            RoleMenuPermission::class,
            'role_id'
        );
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function syncMenuPermissions(array $items): void
    {
        app(RoleMenuPermissionSyncer::class)->sync($this, $items);
        $this->flushUserPermissionCaches();
    }

    private function flushUserPermissionCaches(): void
    {
        $related = $this->users()->getRelated();
        $qualifiedKey = $related->qualifyColumn($related->getKeyName());
        $userIds = $this->users()->pluck($qualifiedKey);

        foreach ($userIds as $id) {
            Cache::forget('aurix:' . $id . ':roles');
            Cache::forget('aurix:' . $id . ':permissions');
            Cache::forget('aurix:menu_access:' . $id);
        }

        app(MenuAccessService::class)->bumpCacheVersion();
    }
}
