<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'route',
        'icon',
        'sort_order',
        'parent_id',
        'permission_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.menus', 'menus');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RoleMenuPermission::class, 'menu_id');
    }
}
