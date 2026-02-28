<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'name',
        'email',
        'avatar',
        'token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo($this->getUserModelClass());
    }

    protected function getUserModelClass(): string
    {
        // Try to get from auth config first
        $model = config('auth.providers.users.model');
        
        if ($model && class_exists($model)) {
            return $model;
        }
        
        // Fallback to common locations
        $possibleModels = [
            'App\\Models\\User',
            'App\\User',
        ];
        
        foreach ($possibleModels as $possibleModel) {
            if (class_exists($possibleModel)) {
                return $possibleModel;
            }
        }
        
        // Last resort - return the config value or default
        // This will fail at runtime if the class doesn't exist, but won't break during autoloading
        return $model ?: 'App\\Models\\User';
    }
}
