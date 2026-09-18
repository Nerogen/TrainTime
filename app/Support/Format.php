<?php

declare(strict_types=1);

namespace App\Support;

final class Format
{
    private const array WEEKDAYS = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    public static function days(array $weekdays): string
    {
        if ($weekdays === []) {
            return '—';
        }

        sort($weekdays);

        return self::shorthand($weekdays)
            ?? implode(', ', array_map(static fn (int $w): string => self::WEEKDAYS[$w], $weekdays));
    }

    public static function period(?string $from, ?string $to): string
    {
        if ($from === null || $to === null) {
            return '—';
        }

        return self::date($from).' – '.self::date($to);
    }

    public static function time(string $time): string
    {
        return substr($time, 0, 5);
    }

    private static function date(string $date): string
    {
        return implode('.', array_reverse(explode('-', $date)));
    }

    private static function shorthand(array $weekdays): ?string
    {
        return match ($weekdays) {
            [0, 1, 2, 3, 4, 5, 6] => 'Все дни недели',
            [0, 1, 2, 3, 4] => 'Рабочие дни',
            [5, 6] => 'Выходные',
            default => null,
        };
    }
}
