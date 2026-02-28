<?php

declare(strict_types=1);

namespace Aurix\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Aurix\Models\Concerns\HasAurixRoles;
use Aurix\Models\Concerns\HasSocialAccounts;

class User extends Authenticatable
{
    use HasAurixRoles, HasSocialAccounts;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
