<?php $this->extend('layouts.app') ?>
<?php use App\Support\Calendar ?>
<?php use App\Support\Format ?>

<h1>Поезд №<?= e($trip->train) ?></h1>

<p class="muted">
    <?= e($trip->fromStation) ?> — <?= e($trip->toStation) ?>,
    отправление в <?= e(Format::time($trip->departureTime)) ?>.
    Всего дней: <?= $trip->runs ?>
    (<?= e(Format::period($trip->firstDate, $trip->lastDate)) ?>).
</p>

<form class="form card" method="post" action="/schedules/<?= $trip->id ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="month" value="<?= e($month) ?>">

    <div class="calendar__nav">
        <a class="link" href="/schedules/<?= $trip->id ?>?month=<?= e($previous) ?>">← предыдущий</a>
        <strong><?= e(Calendar::monthName($month)) ?></strong>
        <a class="link" href="/schedules/<?= $trip->id ?>?month=<?= e($next) ?>">следующий →</a>
    </div>

    <table class="calendar">
        <thead>
        <tr>
            <th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Вс</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (Calendar::grid($month) as $week): ?>
            <tr>
                <?php foreach ($week as $date): ?>
                    <td class="calendar__cell">
                        <?php if ($date !== null): ?>
                            <label class="calendar__day">
                                <input type="checkbox" name="dates[]" value="<?= $date ?>"
                                       <?= isset($times[$date]) ? 'checked' : '' ?>>
                                <span><?= (int) substr($date, 8) ?></span>
                            </label>
                            <input class="calendar__time" type="time" name="times[<?= $date ?>]"
                                   value="<?= e(Format::time($times[$date] ?? $trip->departureTime)) ?>">
                        <?php endif ?>
                    </td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <button class="button" type="submit">Сохранить месяц</button>
    <p class="muted">Сохраняется только открытый месяц: снятая галочка удаляет отправление, время можно задать своё.</p>
</form>

<p><a class="link" href="/schedules">← ко всем расписаниям</a></p>
