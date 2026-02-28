<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.permissions', 'permissions');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('aurix.tables.role_permissions', 'role_permissions')
        );
    }
}
