<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'bool',
        'published_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return (string) config('aurix.tables.posts', 'auth_posts');
    }
}
