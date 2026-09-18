<?php

declare(strict_types=1);

use App\Core\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<'SQL'
            CREATE TABLE routes (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                from_station VARCHAR(100) NOT NULL,
                to_station   VARCHAR(100) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY routes_direction_unique (from_station, to_station)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS routes');
    }
};
