<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleMenuPermission extends Model
{
    protected $table = 'role_menu_permissions';

    protected $fillable = [
        'role_id',
        'menu_id',
        'can_create',
        'can_update',
        'can_delete',
        'can_edit',
    ];

    protected $casts = [
        'can_create' => 'bool',
        'can_update' => 'bool',
        'can_delete' => 'bool',
        'can_edit' => 'bool',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.role_menu_permissions', 'role_menu_permissions');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
