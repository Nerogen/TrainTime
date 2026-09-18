<?php

declare(strict_types=1);

use App\Core\Database\Migration;

return new class implements Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            <<<'SQL'
            CREATE TABLE departures (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                trip_id        INT UNSIGNED NOT NULL,
                `date`         DATE NOT NULL,
                departure_time TIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY departures_trip_date_unique (trip_id, `date`),
                KEY departures_date_index (`date`),
                CONSTRAINT departures_trip_id_foreign
                    FOREIGN KEY (trip_id) REFERENCES trips (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
            SQL
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS departures');
    }
};
