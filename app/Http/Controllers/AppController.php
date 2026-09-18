<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Http\Flash;
use App\Core\Http\Request;
use App\Core\Routing\RouteNotFoundException;
use App\Core\View\View;
use App\Dto\TripFilter;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\StoreTrainRequest;
use App\Http\Requests\UpdateMonthRequest;
use App\Models\Trip;
use App\Services\ScheduleService;
use App\Support\ValidatesDates;
use DateMalformedStringException;
use DateTimeImmutable;
use Throwable;

final class AppController
{
    use ValidatesDates;

    private const int PER_PAGE = 10;

    private readonly Request $request;

    private readonly ScheduleService $schedules;

    public function __construct()
    {
        $this->request = Request::capture();
        $this->schedules = new ScheduleService();
    }

    public function index(): View
    {
        $filters = new TripFilter(
            $this->request->query('train'),
            $this->request->query('from'),
            $this->request->query('to'),
            self::date($this->request->query('date')),
        );

        return View::make('home', [
            'title' => 'Поиск расписания',
            'filters' => $filters,
            'stations' => $this->schedules->stations(),
            'results' => $this->schedules->search($filters),
        ]);
    }

    public function schedules(): View
    {
        $total = $this->schedules->total();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, (int) $this->request->query('page')), $pages);

        return View::make('schedules.index', [
            'title' => 'Информация о расписаниях',
            'rows' => $this->schedules->page($page, self::PER_PAGE),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ]);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function show(string $id): View
    {
        $trip = $this->trip((int) $id);
        $month = self::month($this->request->query('month'))
            ?? substr((string) $trip->firstDate, 0, 7)
            ?: date('Y-m');

        $first = new DateTimeImmutable($month.'-01');

        return View::make('schedules.show', [
            'title' => 'Рейс №'.$trip->train,
            'trip' => $trip,
            'month' => $month,
            'times' => $this->schedules->monthTimes($trip->id, $month),
            'previous' => $first->modify('-1 month')->format('Y-m'),
            'next' => $first->modify('+1 month')->format('Y-m'),
        ]);
    }

    public function create(): View
    {
        $year = (int) $this->request->query('year');
        $current = (int) date('Y');

        return View::make('schedules.create', [
            'title' => 'Добавить расписание',
            'trains' => $this->schedules->trains(),
            'stations' => $this->schedules->stations(),
            'year' => $this->schedules->isValidYear($year) ? $year : $current,
            'years' => range($current - 5, $current + 5),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(): never
    {
        $form = new StoreScheduleRequest($this->request);
        $error = $form->validate();

        if ($error !== null) {
            Flash::error($error);

            redirect('/schedules/create');
        }

        try {
            $tripId = $this->schedules->create($form->train, $form->from, $form->to, $form->time, $form->dates);
        } catch (Throwable $e) {
            $this->fail($e, 'Не удалось сохранить расписание. Попробуйте ещё раз.', '/schedules/create');
        }

        Flash::status('Расписание добавлено: '.count($form->dates).' отправлений.');

        redirect('/schedules/'.$tripId);
    }

    /**
     * @throws Throwable
     */
    public function updateMonth(string $id): never
    {
        $trip = $this->trip((int) $id);
        $form = new UpdateMonthRequest($this->request);
        $error = $form->validate();

        if ($error !== null) {
            Flash::error($error);

            redirect('/schedules/'.$trip->id);
        }

        $back = '/schedules/'.$trip->id.'?month='.$form->month;

        try {
            $this->schedules->saveMonth($trip->id, $form->month, $form->times($trip->departureTime));
        } catch (Throwable $e) {
            $this->fail($e, 'Не удалось сохранить месяц. Попробуйте ещё раз.', $back);
        }

        Flash::status('Месяц сохранён.');

        redirect($back);
    }

    public function storeTrain(): never
    {
        $form = new StoreTrainRequest($this->request);
        $error = $form->validate();

        if ($error !== null) {
            Flash::error($error);

            redirect('/schedules/create');
        }

        try {
            $added = $this->schedules->addTrain($form->train);
        } catch (Throwable $e) {
            $this->fail($e, 'Не удалось добавить поезд. Попробуйте ещё раз.', '/schedules/create');
        }

        if ($added) {
            Flash::status('Поезд №'.$form->train.' добавлен.');
        } else {
            Flash::error('Поезд №'.$form->train.' уже есть.');
        }

        redirect('/schedules/create');
    }

    private function fail(Throwable $e, string $message, string $url): never
    {
        error_log((string) $e);

        Flash::error($message);

        redirect($url);
    }

    private function trip(int $id): Trip
    {
        $trip = $this->schedules->trip($id);

        if ($trip === null) {
            throw new RouteNotFoundException('Trip '.$id.' not found.');
        }

        return $trip;
    }
}
