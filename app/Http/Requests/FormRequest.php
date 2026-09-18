<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Core\Http\Request;
use App\Support\ValidatesDates;

abstract class FormRequest
{
    use ValidatesDates;

    protected const int MAX_NAME = 100;

    public function __construct(protected readonly Request $request)
    {
    }

    abstract public function validate(): ?string;
}
