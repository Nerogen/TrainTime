<?php use App\Support\Format ?>

<table class="table">
    <thead>
    <tr>
        <th>Поезд</th>
        <th>Маршрут</th>
        <th>Дни</th>
        <th>Период</th>
        <th>Время</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $trip): ?>
        <tr>
            <td class="table__train"><a href="/schedules/<?= $trip->id ?>">№<?= e($trip->train) ?></a></td>
            <td><?= e($trip->fromStation) ?> — <?= e($trip->toStation) ?></td>
            <td><?= e(Format::days($trip->weekdays)) ?></td>
            <td class="table__period"><?= e(Format::period($trip->firstDate, $trip->lastDate)) ?></td>
            <td><time><?= e(Format::time($trip->departureTime)) ?></time></td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
