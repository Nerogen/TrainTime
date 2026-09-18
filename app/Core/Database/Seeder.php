<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;

interface Seeder
{
    public function run(PDO $pdo): void;
}
