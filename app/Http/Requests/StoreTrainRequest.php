<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Http\Request;

final class StoreTrainRequest extends FormRequest
{
    public readonly string $train;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->train = $request->input('train');
    }

    public function validate(): ?string
    {
        return match (true) {
            $this->train === '' => 'Укажите номер поезда.',
            mb_strlen($this->train) > self::MAX_NAME => 'Номер поезда слишком длинный.',
            default => null,
        };
    }
}
