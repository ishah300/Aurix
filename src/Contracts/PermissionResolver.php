<?php

declare(strict_types=1);

namespace Aurix\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface PermissionResolver
{
    public function hasRole(Authenticatable $user, string $role): bool;

    public function hasPermission(Authenticatable $user, string $permission, array $arguments = []): bool;

    public function isSuperAdmin(Authenticatable $user): bool;
}
