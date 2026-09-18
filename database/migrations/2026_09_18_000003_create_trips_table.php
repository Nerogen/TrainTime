<?php

declare(strict_types=1);

use App\Core\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<'SQL'
            CREATE TABLE trips (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                train_id       INT UNSIGNED NOT NULL,
                route_id       INT UNSIGNED NOT NULL,
                departure_time TIME NOT NULL,
                PRIMARY KEY (id),
                KEY trips_train_id_index (train_id),
                KEY trips_route_id_index (route_id),
                CONSTRAINT trips_train_id_foreign
                    FOREIGN KEY (train_id) REFERENCES trains (id) ON DELETE CASCADE,
                CONSTRAINT trips_route_id_foreign
                    FOREIGN KEY (route_id) REFERENCES routes (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS trips');
    }
};
