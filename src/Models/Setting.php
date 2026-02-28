<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.settings', 'aurix_settings');
    }
}

