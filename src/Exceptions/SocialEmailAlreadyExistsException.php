<?php

declare(strict_types=1);

namespace Aurix\Exceptions;

use RuntimeException;

class SocialEmailAlreadyExistsException extends RuntimeException
{
    public function __construct(
        public readonly string $email,
        ?string $message = null
    ) {
        parent::__construct(
            $message ?? 'An account with this email already exists. Please verify ownership to link providers.'
        );
    }
}
