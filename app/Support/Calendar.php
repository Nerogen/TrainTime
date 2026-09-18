<?php

declare(strict_types=1);

namespace App\Support;

use DateMalformedStringException;
use DateTimeImmutable;

final class Calendar
{
    private const array MONTHS = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь',
    ];

    public static function months(): array
    {
        return self::MONTHS;
    }

    public static function monthName(string $month): string
    {
        [$year, $number] = explode('-', $month);

        return self::MONTHS[(int) $number - 1].' '.$year;
    }

    /**
     *
     * @throws DateMalformedStringException
     */
    public static function grid(string $month): array
    {
        $first = new DateTimeImmutable($month.'-01');
        $cells = array_fill(0, (int) $first->format('N') - 1, null);

        for ($day = 0, $days = (int) $first->format('t'); $day < $days; $day++) {
            $cells[] = $first->modify('+'.$day.' day')->format('Y-m-d');
        }

        return array_chunk(array_pad($cells, (int) ceil(count($cells) / 7) * 7, null), 7);
    }
}
