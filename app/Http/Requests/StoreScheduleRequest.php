<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Http\Request;

final class StoreScheduleRequest extends FormRequest
{
    public readonly string $train;

    public readonly string $from;

    public readonly string $to;

    public readonly string $time;

    /** @var list<string> */
    public readonly array $dates;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->train = $request->input('train');
        $this->from = $request->input('from');
        $this->to = $request->input('to');
        $this->time = $request->input('time');
        $this->dates = self::dates($request->strings('dates'));
    }

    public function validate(): ?string
    {
        return match (true) {
            $this->train === '' || $this->from === '' || $this->to === '' => 'Укажите поезд и обе станции.',
            mb_strlen($this->train) > self::MAX_NAME => 'Номер поезда слишком длинный.',
            mb_strlen($this->from) > self::MAX_NAME || mb_strlen($this->to) > self::MAX_NAME => 'Название станции слишком длинное.',
            $this->from === $this->to => 'Станции отправления и прибытия должны отличаться.',
            self::time($this->time) === null => 'Время отправления — в формате ЧЧ:ММ.',
            $this->dates === [] => 'Отметьте в календаре хотя бы один день.',
            default => null,
        };
    }

    /**
     * @param  list<string>  $raw
     * @return list<string>
     */
    private static function dates(array $raw): array
    {
        $dates = [];

        foreach ($raw as $value) {
            if (self::date($value) !== null) {
                $dates[$value] = true;
            }
        }

        ksort($dates);

        return array_keys($dates);
    }
}
