<?php

declare(strict_types=1);

namespace Aurix\Models\Concerns;

use Aurix\Models\SocialAccount;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSocialAccounts
{
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function hasSocialAccount(string $provider): bool
    {
        return $this->socialAccounts()->where('provider', $provider)->exists();
    }

    public function getSocialAccount(string $provider): ?SocialAccount
    {
        return $this->socialAccounts()->where('provider', $provider)->first();
    }
}
