<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Http\Request;

final class UpdateMonthRequest extends FormRequest
{
    public readonly string $month;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->month = self::month($request->input('month')) ?? '';
    }

    public function validate(): ?string
    {
        return $this->month === '' ? 'Не удалось определить месяц.' : null;
    }

    /** @return array<string, string> */
    public function times(string $fallback): array
    {
        $raw = $this->request->pairs('times');
        $times = [];

        foreach ($this->request->strings('dates') as $date) {
            if (self::date($date) === null || !str_starts_with($date, $this->month.'-')) {
                continue;
            }

            $times[$date] = self::time($raw[$date] ?? null) ?? $fallback;
        }

        return $times;
    }
}
