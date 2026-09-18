<?php

declare(strict_types=1);

namespace App\Core\Database;

use PDO;

abstract class Repository
{
    protected readonly PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }
}
