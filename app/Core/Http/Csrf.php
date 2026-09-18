<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Csrf
{
    private const KEY = '_token';

    public static function token(): string
    {
        return $_SESSION[self::KEY] ??= bin2hex(random_bytes(32));
    }

    public static function check(mixed $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
}
