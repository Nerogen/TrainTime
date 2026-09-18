<?php

declare(strict_types=1);

namespace App\Facades;

use App\Core\Routing\Router;

final class Route extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
