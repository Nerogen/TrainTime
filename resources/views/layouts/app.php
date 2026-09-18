<?php use App\Core\Http\Flash ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'TrainTime') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<?php $path = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?>

<header class="header">
    <button class="burger" id="burger" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Меню">
        <span></span><span></span><span></span>
    </button>
    <a class="header__logo" href="/">TrainTime</a>
</header>

<aside class="sidebar" id="sidebar" hidden>
    <nav class="sidebar__nav">
        <a class="sidebar__link<?= $path === '/' ? ' is-active' : '' ?>" href="/">Поиск</a>
        <a class="sidebar__link<?= $path === '/schedules' ? ' is-active' : '' ?>" href="/schedules">Информация о расписаниях</a>
        <a class="sidebar__link<?= $path === '/schedules/create' ? ' is-active' : '' ?>" href="/schedules/create">Добавить расписание</a>
    </nav>
</aside>

<div class="backdrop" id="backdrop" hidden></div>

<main class="container">
    <?php if ($status = Flash::pullStatus()): ?>
        <p class="alert alert--ok"><?= e($status) ?></p>
    <?php endif ?>

    <?php if ($error = Flash::pullError()): ?>
        <p class="alert"><?= e($error) ?></p>
    <?php endif ?>

    <?= $content ?>
</main>

<script src="/js/app.js" defer></script>
</body>
</html>
