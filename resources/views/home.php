<?php $this->extend('layouts.app') ?>

<h1>Поиск расписания</h1>

<form class="filters" method="get" action="/">
    <label class="field">
        <span>Номер поезда</span>
        <input type="text" name="train" value="<?= e($filters->train ?? '') ?>"
               placeholder="например, 45" maxlength="100">
    </label>

    <label class="field">
        <span>Откуда</span>
        <input type="text" name="from" list="stations" value="<?= e($filters->from ?? '') ?>" maxlength="100">
    </label>

    <label class="field">
        <span>Куда</span>
        <input type="text" name="to" list="stations" value="<?= e($filters->to ?? '') ?>" maxlength="100">
    </label>

    <label class="field">
        <span>Дата</span>
        <input type="date" name="date" value="<?= e($filters->date ?? '') ?>">
    </label>

    <div class="filters__actions">
        <button class="button" type="submit">Найти</button>
        <a class="link" href="/">Сбросить</a>
    </div>
</form>

<datalist id="stations">
    <?php foreach ($stations as $station): ?>
        <option value="<?= e($station) ?>"></option>
    <?php endforeach ?>
</datalist>

<?php if ($filters->date === null): ?>
    <p class="muted">Без даты показаны все рейсы маршрута, а не только те, что идут в конкретный день.</p>
<?php endif ?>
<?php if ($results === []): ?>
    <p class="empty">Ничего не нашлось. Попробуйте ослабить фильтры.</p>
<?php else: ?>
    <?php $this->include('schedules._table', ['rows' => $results]) ?>
<?php endif ?>
