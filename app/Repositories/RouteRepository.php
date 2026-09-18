<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\Repository;
use App\Models\Route;
use PDO;

final class RouteRepository extends Repository
{
    public function stations(): array
    {
        return $this->pdo
            ->query('SELECT from_station AS s FROM routes UNION SELECT to_station FROM routes ORDER BY s')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function firstOrCreate(string $from, string $to): Route
    {
        $statement = $this->pdo->prepare(
            'SELECT id, from_station, to_station FROM routes WHERE from_station = ? AND to_station = ?'
        );
        $statement->execute([$from, $to]);

        $row = $statement->fetch();

        if ($row !== false) {
            return Route::fromRow($row);
        }

        $this->pdo->prepare('INSERT INTO routes (from_station, to_station) VALUES (?, ?)')->execute([$from, $to]);

        return new Route((int) $this->pdo->lastInsertId(), $from, $to);
    }
}
