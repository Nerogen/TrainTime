<?php $this->extend('layouts.app') ?>
<?php use App\Support\Calendar ?>

<h1>Добавить расписание</h1>

<form class="picker card" method="get" action="/schedules/create">
    <label class="field">
        <span>Год расписания</span>
        <select name="year">
            <?php foreach ($years as $option): ?>
                <option value="<?= $option ?>" <?= $option === $year ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach ?>
        </select>
    </label>

    <button class="button button--ghost" type="submit">Показать</button>
    <span class="muted">Календарь ниже покажет выбранный год целиком.</span>
</form>

<form class="form card" method="post" action="/schedules">
    <?= csrf_field() ?>

    <h2>Рейс целиком</h2>
    <p class="muted">Поезд и маршрут создадутся сами, если их ещё нет.</p>

    <div class="grid">
        <label class="field">
            <span>Номер поезда</span>
            <input type="text" name="train" list="trains" required maxlength="100" placeholder="45">
        </label>

        <label class="field">
            <span>Время отправления</span>
            <input type="time" name="time" required>
        </label>

        <label class="field">
            <span>Откуда</span>
            <input type="text" name="from" list="stations" required maxlength="100" placeholder="Минск">
        </label>

        <label class="field">
            <span>Куда</span>
            <input type="text" name="to" list="stations" required maxlength="100" placeholder="Брест">
        </label>
    </div>

    <div class="picker" id="picker">
        <span class="muted">Отметить весь год:</span>
        <?php foreach (['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $weekday => $label): ?>
            <button class="button button--ghost" type="button" data-weekday="<?= $weekday ?>"><?= $label ?></button>
        <?php endforeach ?>
        <button class="button button--ghost" type="button" data-clear>Снять все</button>
    </div>

    <div class="months">
        <?php foreach (Calendar::months() as $index => $name): ?>
            <?php $month = sprintf('%04d-%02d', $year, $index + 1) ?>
            <section class="month">
                <h3><?= e($name) ?></h3>

                <table class="calendar calendar--compact">
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
                                            <input type="checkbox" name="dates[]" value="<?= $date ?>">
                                            <span><?= (int) substr($date, 8) ?></span>
                                        </label>
                                    <?php endif ?>
                                </td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </section>
        <?php endforeach ?>
    </div>

    <button class="button" type="submit">Добавить расписание</button>
</form>

<form class="form card" method="post" action="/trains">
    <?= csrf_field() ?>

    <h2>Только поезд</h2>
    <p class="muted">Если нужно завести номер заранее, без рейса.</p>

    <label class="field">
        <span>Номер поезда</span>
        <input type="text" name="train" required maxlength="100" placeholder="112">
    </label>

    <button class="button button--ghost" type="submit">Добавить поезд</button>
</form>

<datalist id="trains">
    <?php foreach ($trains as $train): ?>
        <option value="<?= e($train->name) ?>"></option>
    <?php endforeach ?>
</datalist>

<datalist id="stations">
    <?php foreach ($stations as $station): ?>
        <option value="<?= e($station) ?>"></option>
    <?php endforeach ?>
</datalist>
