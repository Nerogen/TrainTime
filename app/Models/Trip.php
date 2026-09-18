<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Trip
{
    /** @param list<int> $weekdays */
    private function __construct(
        public int     $id,
        public string  $train,
        public string  $fromStation,
        public string  $toStation,
        public string  $departureTime,
        public ?string $firstDate,
        public ?string $lastDate,
        public array   $weekdays,
        public int     $runs,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        $weekdays = (string) ($row['weekdays'] ?? '');

        return new self(
            (int) $row['id'],
            (string) $row['train'],
            (string) $row['from_station'],
            (string) $row['to_station'],
            (string) $row['departure_time'],
            isset($row['first_date']) ? (string) $row['first_date'] : null,
            isset($row['last_date']) ? (string) $row['last_date'] : null,
            $weekdays === '' ? [] : array_map('intval', explode(',', $weekdays)),
            (int) $row['runs'],
        );
    }
}
