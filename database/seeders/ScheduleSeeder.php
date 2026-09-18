<?php

declare(strict_types=1);

use App\Core\Database\Seeder;

return new class implements Seeder {
    private const TABLES = ['departures', 'trips', 'trains', 'routes'];

    private const FIRST_YEAR = 2022;

    private const LAST_YEAR = 2026;

    public function run(PDO $pdo): void
    {
        $this->truncate($pdo);

        $insertRoute = $pdo->prepare('INSERT INTO routes (from_station, to_station) VALUES (?, ?)');
        $insertTrain = $pdo->prepare('INSERT INTO trains (name) VALUES (?)');
        $insertTrip = $pdo->prepare('INSERT INTO trips (train_id, route_id, departure_time) VALUES (?, ?, ?)');
        $insertDeparture = $pdo->prepare('INSERT INTO departures (trip_id, `date`, departure_time) VALUES (?, ?, ?)');

        $routeIds = [];

        foreach ($this->schedule() as $row) {
            [$fromStation, $toStation] = $row['route'];
            $key = $fromStation.'>'.$toStation;

            if (!isset($routeIds[$key])) {
                $insertRoute->execute([$fromStation, $toStation]);
                $routeIds[$key] = (int) $pdo->lastInsertId();
            }

            $insertTrain->execute([$row['train']]);
            $trainId = (int) $pdo->lastInsertId();

            $insertTrip->execute([$trainId, $routeIds[$key], $row['time']]);
            $tripId = (int) $pdo->lastInsertId();

            foreach ($this->dates($row) as $date) {
                $insertDeparture->execute([$tripId, $date, $row['time']]);
            }
        }
    }

    private function dates(array $row): array
    {
        $days = [];

        foreach ($row['weekdays'] as $weekday) {
            [$day, $recurrence] = is_array($weekday) ? $weekday : [$weekday, 'any'];

            $days[$day] = $recurrence;
        }

        $dates = [];

        for ($year = self::FIRST_YEAR; $year <= self::LAST_YEAR; $year++) {
            $dates = array_merge($dates, $this->expand($year.'-'.$row['from'], $year.'-'.$row['to'], $days));
        }

        return array_diff($dates, $row['skip']);
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateMalformedPeriodStringException
     */
    private function expand(string $from, string $to, array $days): array
    {
        $period = new DatePeriod(
            new DateTimeImmutable($from),
            new DateInterval('P1D'),
            (new DateTimeImmutable($to))->modify('+1 day'),
        );

        $dates = [];

        foreach ($period as $date) {
            $rule = $days[(int) $date->format('N') - 1] ?? null;

            if ($rule === 'any' || ($rule === 'even_day' && (int) $date->format('j') % 2 === 0)) {
                $dates[] = $date->format('Y-m-d');
            }
        }

        return $dates;
    }

    private function schedule(): array
    {
        return [
            [
                'train' => '21',
                'route' => ['Минск', 'Москва'],
                'time' => '09:00:00',
                'from' => '01-01',
                'to' => '12-31',
                'weekdays' => [0, 1, 2, 3, 4, 5, 6],
                'skip' => [],
            ],
            [
                'train' => '22',
                'route' => ['Минск', 'Варшава'],
                'time' => '10:00:00',
                'from' => '01-01',
                'to' => '12-31',
                'weekdays' => [0, 1, 2, 3, 4],
                'skip' => [],
            ],
            [
                'train' => '45',
                'route' => ['Минск', 'Брест'],
                'time' => '11:00:00',
                'from' => '01-01',
                'to' => '12-31',
                'weekdays' => [0, 4, [6, 'even_day']],
                'skip' => [],
            ],
            [
                'train' => '67',
                'route' => ['Минск', 'Гомель'],
                'time' => '12:00:00',
                'from' => '06-01',
                'to' => '08-31',
                'weekdays' => [2, 3],
                'skip' => [],
            ],
            [
                'train' => '35',
                'route' => ['Минск', 'Гомель'],
                'time' => '12:00:00',
                'from' => '09-01',
                'to' => '12-31',
                'weekdays' => [5, 6],
                'skip' => [],
            ],
            [
                'train' => '39',
                'route' => ['Минск', 'Прага'],
                'time' => '13:00:00',
                'from' => '01-01',
                'to' => '12-31',
                'weekdays' => [5],
                'skip' => ['2022-03-26', '2022-04-16'],
            ],
        ];
    }

    private function truncate(PDO $pdo): void
    {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach (self::TABLES as $table) {
            $pdo->exec('TRUNCATE TABLE '.$table);
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
};
