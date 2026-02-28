<?php

declare(strict_types=1);

namespace Aurix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class SocialProvider extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'enabled',
        'client_id',
        'client_secret',
        'redirect',
        'scopes',
        'requires_package',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'enabled' => 'bool',
        'requires_package' => 'bool',
    ];

    public function getTable(): string
    {
        return (string) config(
            'aurix.tables.social_providers',
            (string) config('aurix.social.providers_table', 'aurix_social_providers')
        );
    }

    protected function clientSecret(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? '********' : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    protected function clientId(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => $value,
        );
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        // Do not expose raw client_secret
        if (! empty($this->getAttributes()['client_secret'])) {
            $arr['client_secret'] = '********';
        } else {
            $arr['client_secret'] = null;
        }

        return $arr;
    }

    public function revealSecret(): ?string
    {
        $v = $this->getAttributes()['client_secret'] ?? null;
        return $v ? decrypt($v) : null;
    }
}
