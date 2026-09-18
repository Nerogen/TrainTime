<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\TripFilter;
use App\Models\Trip;
use App\Repositories\RouteRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\TrainRepository;
use DateMalformedStringException;
use DateTimeImmutable;
use Throwable;

final class ScheduleService
{
    private const int MIN_YEAR = 2000;

    private const int MAX_YEAR = 2100;

    private readonly ScheduleRepository $schedules;

    private readonly TrainRepository $trains;

    private readonly RouteRepository $routes;

    public function __construct()
    {
        $this->schedules = new ScheduleRepository();
        $this->trains = new TrainRepository();
        $this->routes = new RouteRepository();
    }

    public function search(TripFilter $filter): array
    {
        return $this->schedules->search($filter);
    }

    public function page(int $page, int $perPage): array
    {
        return $this->schedules->paginate($page, $perPage);
    }

    public function total(): int
    {
        return $this->schedules->total();
    }

    public function trip(int $id): ?Trip
    {
        return $this->schedules->find($id);
    }

    public function stations(): array
    {
        return $this->routes->stations();
    }

    public function trains(): array
    {
        return $this->trains->all();
    }

    public function isValidYear(int $year): bool
    {
        return $year >= self::MIN_YEAR && $year <= self::MAX_YEAR;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function monthTimes(int $tripId, string $month): array
    {
        [$from, $to] = self::monthBounds($month);

        return $this->schedules->between($tripId, $from, $to);
    }

    /**
     * @throws Throwable
     */
    public function create(string $train, string $from, string $to, string $departureTime, array $dates): int
    {
        return $this->schedules->create(
            $this->trains->firstOrCreate($train)->id,
            $this->routes->firstOrCreate($from, $to)->id,
            $departureTime,
            $dates,
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws Throwable
     */
    public function saveMonth(int $tripId, string $month, array $times): void
    {
        [$from, $to] = self::monthBounds($month);

        $this->schedules->saveMonth($tripId, $from, $to, $times);
    }

    public function addTrain(string $name): bool
    {
        if ($this->trains->findByName($name) !== null) {
            return false;
        }

        $this->trains->create($name);

        return true;
    }

    /**
     * @throws DateMalformedStringException
     */
    private static function monthBounds(string $month): array
    {
        $first = new DateTimeImmutable($month.'-01');

        return [$first->format('Y-m-01'), $first->format('Y-m-t')];
    }
}
