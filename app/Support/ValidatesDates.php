<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;

trait ValidatesDates
{
    protected static function date(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $value : null;
    }

    protected static function month(?string $value): ?string
    {
        return $value !== null && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value) === 1 ? $value : null;
    }

    protected static function time(?string $value): ?string
    {
        return $value !== null && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : null;
    }
}
