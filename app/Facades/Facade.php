<?php

declare(strict_types=1);

namespace App\Facades;

abstract class Facade
{
    private static array $instances = [];

    abstract protected static function getFacadeAccessor(): string;

    public static function __callStatic(string $method, array $arguments): mixed
    {
        $class = static::getFacadeAccessor();

        return (self::$instances[$class] ??= new $class())->{$method}(...$arguments);
    }
}
