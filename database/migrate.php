<?php

declare(strict_types=1);

use App\Core\Database\Connection;
use App\Core\Database\Migration;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH.'/vendor/autoload.php';

$pdo = Connection::get();

$pdo->exec(
    <<<'SQL'
    CREATE TABLE IF NOT EXISTS migrations (
        id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch     INT UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY migrations_migration_unique (migration)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
    SQL
);

$command = $argv[1] ?? 'migrate';

match ($command) {
    'migrate' => migrateUp($pdo),
    'rollback' => migrateDown($pdo),
    default => exit(sprintf("Неизвестная команда [%s]. Доступны: migrate, rollback.\n", $command)),
};

function migrateUp(PDO $pdo): void
{
    $applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
    $batch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() + 1;

    $pending = array_filter(
        glob(BASE_PATH.'/database/migrations/*.php'),
        static fn (string $file): bool => !in_array(basename($file, '.php'), $applied, true),
    );

    if ($pending === []) {
        echo "Новых миграций нет.\n";

        return;
    }

    sort($pending);

    $insert = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');

    foreach ($pending as $file) {
        $name = basename($file, '.php');

        migration($file)->up($pdo);
        $insert->execute([$name, $batch]);

        echo "Применена: {$name}\n";
    }
}

function migrateDown(PDO $pdo): void
{
    $batch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn();

    if ($batch === 0) {
        echo "Откатывать нечего.\n";

        return;
    }

    $statement = $pdo->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
    $statement->execute([$batch]);

    $delete = $pdo->prepare('DELETE FROM migrations WHERE migration = ?');

    foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $name) {
        migration(BASE_PATH.'/database/migrations/'.$name.'.php')->down($pdo);
        $delete->execute([$name]);

        echo "Откачена: {$name}\n";
    }
}

function migration(string $file): Migration
{
    $migration = require $file;

    if (!$migration instanceof Migration) {
        exit(sprintf("Файл %s должен возвращать реализацию Migration.\n", basename($file)));
    }

    return $migration;
}
