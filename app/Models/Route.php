<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Route
{
    public function __construct(
        public int    $id,
        public string $fromStation,
        public string $toStation,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self((int) $row['id'], (string) $row['from_station'], (string) $row['to_station']);
    }
}
