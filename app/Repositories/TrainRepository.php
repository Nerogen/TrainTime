<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\Repository;
use App\Models\Train;

final class TrainRepository extends Repository
{
    public function all(): array
    {
        $rows = $this->pdo->query('SELECT id, name FROM trains ORDER BY CAST(name AS UNSIGNED), name')->fetchAll();

        return array_map(Train::fromRow(...), $rows);
    }

    public function findByName(string $name): ?Train
    {
        $statement = $this->pdo->prepare('SELECT id, name FROM trains WHERE name = ?');
        $statement->execute([$name]);

        $row = $statement->fetch();

        return $row === false ? null : Train::fromRow($row);
    }

    public function create(string $name): Train
    {
        $this->pdo->prepare('INSERT INTO trains (name) VALUES (?)')->execute([$name]);

        return new Train((int) $this->pdo->lastInsertId(), $name);
    }

    public function firstOrCreate(string $name): Train
    {
        return $this->findByName($name) ?? $this->create($name);
    }
}
