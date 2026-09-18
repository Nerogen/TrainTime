<?php

declare(strict_types=1);

use App\Core\Http\Csrf;
use App\Core\Routing\RouteNotFoundException;
use App\Core\View\View;
use App\Facades\Route;

require dirname(__DIR__).'/bootstrap/app.php';

ini_set('display_errors', '0');

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (!in_array($method, ['GET', 'HEAD'], true) && !Csrf::check($_POST['_token'] ?? null)) {
    http_response_code(403);
    echo View::make('errors.404', ['title' => 'Ошибка'])->render();

    return;
}

try {
    $result = Route::dispatch(
        $method,
        parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
    );
} catch (RouteNotFoundException) {
    http_response_code(404);
    echo View::make('errors.404')->render();

    return;
} catch (Throwable $e) {
    error_log((string) $e);

    http_response_code(500);
    echo View::make('errors.500', ['title' => 'Ошибка'])->render();

    return;
}

echo $result instanceof View ? $result->render() : (string) $result;
