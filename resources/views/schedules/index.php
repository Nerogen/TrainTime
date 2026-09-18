<?php $this->extend('layouts.app') ?>

<h1>Информация о расписаниях</h1>

<p class="muted">Всего рейсов: <?= e($total) ?>. Клик по номеру откроет календарь рейса.</p>

<?php if ($rows === []): ?>
    <p class="empty">Расписаний пока нет. <a href="/schedules/create">Добавить первое</a>.</p>
<?php else: ?>
    <?php $this->include('schedules._table') ?>

    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Страницы">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="pagination__item is-active" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a class="pagination__item" href="/schedules?page=<?= $i ?>"><?= $i ?></a>
                <?php endif ?>
            <?php endfor ?>
        </nav>
    <?php endif ?>
<?php endif ?>
