<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Flash
{
    private const STATUS = 'status';

    private const ERROR = 'error';

    public static function status(string $message): void
    {
        $_SESSION[self::STATUS] = $message;
    }

    public static function error(string $message): void
    {
        $_SESSION[self::ERROR] = $message;
    }

    public static function pullStatus(): ?string
    {
        return self::pull(self::STATUS);
    }

    public static function pullError(): ?string
    {
        return self::pull(self::ERROR);
    }

    private static function pull(string $key): ?string
    {
        $message = $_SESSION[$key] ?? null;

        unset($_SESSION[$key]);

        return is_string($message) ? $message : null;
    }
}
