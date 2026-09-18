<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database\Repository;
use App\Dto\TripFilter;
use App\Models\Trip;
use PDO;
use Throwable;

final class ScheduleRepository extends Repository
{
    private const string SUMMARY = <<<'SQL'
        SELECT t.id,
               tr.name AS train,
               r.from_station,
               r.to_station,
               COALESCE(MAX(CASE WHEN d.date = :onDate THEN d.departure_time END), t.departure_time) AS departure_time,
               MIN(d.date) AS first_date,
               MAX(d.date) AS last_date,
               COUNT(d.id) AS runs,
               GROUP_CONCAT(DISTINCT WEEKDAY(d.date) ORDER BY WEEKDAY(d.date)) AS weekdays
          FROM trips t
          JOIN trains tr ON tr.id = t.train_id
          JOIN routes r ON r.id = t.route_id
          LEFT JOIN departures d ON d.trip_id = t.id
        SQL;

    private const string GROUPING = ' GROUP BY t.id, tr.name, r.from_station, r.to_station, t.departure_time';

    public function search(TripFilter $filter): array
    {
        $params = [':onDate' => $filter->date];
        $conditions = [];

        if ($filter->train !== null) {
            $conditions[] = 'tr.name = :train';
            $params[':train'] = $filter->train;
        }

        if ($filter->from !== null) {
            $conditions[] = 'r.from_station = :fromStation';
            $params[':fromStation'] = $filter->from;
        }

        if ($filter->to !== null) {
            $conditions[] = 'r.to_station = :toStation';
            $params[':toStation'] = $filter->to;
        }

        if ($filter->date !== null) {
            $conditions[] = 'EXISTS (SELECT 1 FROM departures dd WHERE dd.trip_id = t.id AND dd.date = :runsOn)';
            $params[':runsOn'] = $filter->date;
        }

        $sql = self::SUMMARY
            .($conditions === [] ? '' : ' WHERE '.implode(' AND ', $conditions))
            .self::GROUPING
            .' ORDER BY departure_time, CAST(tr.name AS UNSIGNED)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return array_map(Trip::fromRow(...), $statement->fetchAll());
    }

    public function paginate(int $page, int $perPage): array
    {
        $statement = $this->pdo->prepare(
            self::SUMMARY.self::GROUPING.' ORDER BY CAST(tr.name AS UNSIGNED) LIMIT :limit OFFSET :offset'
        );

        $statement->bindValue(':onDate', null, PDO::PARAM_NULL);
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $statement->execute();

        return array_map(Trip::fromRow(...), $statement->fetchAll());
    }

    public function total(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM trips')->fetchColumn();
    }

    public function find(int $id): ?Trip
    {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT t.id,
                   tr.name AS train,
                   r.from_station,
                   r.to_station,
                   t.departure_time,
                   MIN(d.date) AS first_date,
                   MAX(d.date) AS last_date,
                   COUNT(d.id) AS runs,
                   GROUP_CONCAT(DISTINCT WEEKDAY(d.date) ORDER BY WEEKDAY(d.date)) AS weekdays
              FROM trips t
              JOIN trains tr ON tr.id = t.train_id
              JOIN routes r ON r.id = t.route_id
              LEFT JOIN departures d ON d.trip_id = t.id
             WHERE t.id = ?
             GROUP BY t.id, tr.name, r.from_station, r.to_station, t.departure_time
            SQL
        );

        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row === false ? null : Trip::fromRow($row);
    }

    public function between(int $tripId, string $from, string $to): array
    {
        $statement = $this->pdo->prepare(
            'SELECT `date`, departure_time FROM departures WHERE trip_id = ? AND `date` BETWEEN ? AND ? ORDER BY `date`'
        );

        $statement->execute([$tripId, $from, $to]);

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function create(int $trainId, int $routeId, string $departureTime, array $dates): int
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->prepare('INSERT INTO trips (train_id, route_id, departure_time) VALUES (?, ?, ?)')
                ->execute([$trainId, $routeId, $departureTime]);

            $tripId = (int) $this->pdo->lastInsertId();

            $this->fill($tripId, array_fill_keys($dates, $departureTime));

            $this->pdo->commit();

            return $tripId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }

    public function saveMonth(int $tripId, string $from, string $to, array $times): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->prepare('DELETE FROM departures WHERE trip_id = ? AND `date` BETWEEN ? AND ?')
                ->execute([$tripId, $from, $to]);

            $this->fill($tripId, $times);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }

    private function fill(int $tripId, array $times): void
    {
        $insert = $this->pdo->prepare(
            <<<'SQL'
            INSERT INTO departures (trip_id, `date`, departure_time) VALUES (?, ?, ?) AS new
            ON DUPLICATE KEY UPDATE departure_time = new.departure_time
            SQL
        );

        foreach ($times as $date => $time) {
            $insert->execute([$tripId, $date, $time]);
        }
    }
}
