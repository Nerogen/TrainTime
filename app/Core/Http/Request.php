<?php

declare(strict_types=1);

namespace App\Core\Http;

final readonly class Request
{
    private function __construct(
        private array $query,
        private array $body,
    ) {
    }

    public static function capture(): self
    {
        return new self($_GET, $_POST);
    }

    public function query(string $key): ?string
    {
        $value = trim((string) ($this->query[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    public function input(string $key): string
    {
        return trim((string) ($this->body[$key] ?? ''));
    }

    /** @return list<string> */
    public function strings(string $key): array
    {
        $values = $this->body[$key] ?? null;

        return is_array($values) ? array_values(array_filter($values, 'is_string')) : [];
    }

    /** @return array<string, string> */
    public function pairs(string $key): array
    {
        $values = $this->body[$key] ?? null;

        if (!is_array($values)) {
            return [];
        }

        $pairs = [];

        foreach ($values as $name => $value) {
            if (is_string($value)) {
                $pairs[(string) $name] = $value;
            }
        }

        return $pairs;
    }
}
