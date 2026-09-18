<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class TripFilter
{
    public function __construct(
        public ?string $train = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $date = null,
    ) {
    }
}
