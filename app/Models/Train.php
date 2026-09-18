<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Train
{
    public function __construct(
        public int    $id,
        public string $name,
    ) {
    }

    public static function fromRow(array $row): self
    {
        return new self((int) $row['id'], (string) $row['name']);
    }
}
