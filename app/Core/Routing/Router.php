<?php

declare(strict_types=1);

namespace App\Core\Routing;

final class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, callable|array $action): void
    {
        $this->add('POST', $uri, $action);
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $uri = $this->normalize($uri);

        foreach ($this->routes[strtoupper($method)] ?? [] as $pattern => $action) {
            if (preg_match($this->toRegex($pattern), $uri, $matches) === 1) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->call($action, $params);
            }
        }

        throw new RouteNotFoundException(sprintf('No route matched %s %s.', $method, $uri));
    }

    private function add(string $method, string $uri, callable|array $action): void
    {
        $this->routes[$method][$this->normalize($uri)] = $action;
    }

    private function call(callable|array $action, array $params): mixed
    {
        if (is_array($action)) {
            [$class, $method] = $action;
            $action = [new $class(), $method];
        }

        return $action(...array_values($params));
    }

    private function normalize(string $uri): string
    {
        return '/'.trim($uri, '/');
    }

    private function toRegex(string $pattern): string
    {
        $parts = preg_split(
            '#(\{[a-zA-Z_][a-zA-Z0-9_]*\})#',
            $pattern,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY,
        );

        $regex = '';

        foreach ($parts as $part) {
            $regex .= str_starts_with($part, '{')
                ? '(?P<'.trim($part, '{}').'>[^/]+)'
                : preg_quote($part, '#');
        }

        return '#^'.$regex.'$#';
    }
}
