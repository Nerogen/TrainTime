<?php

declare(strict_types=1);

use App\Core\Database\Connection;
use App\Core\Database\Seeder;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/vendor/autoload.php';

$pdo = Connection::get();

$files = glob(BASE_PATH.'/database/seeders/*.php');
sort($files);

foreach ($files as $file) {
    $seeder = require $file;

    if (!$seeder instanceof Seeder) {
        exit(sprintf("Файл %s должен возвращать реализацию Seeder.\n", basename($file)));
    }

    $seeder->run($pdo);

    echo 'Засеяно: '.basename($file, '.php')."\n";
}
