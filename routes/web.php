<?php

declare(strict_types=1);

use App\Facades\Route;
use App\Http\Controllers\AppController;

Route::get('/', [AppController::class, 'index']);

Route::get('/schedules', [AppController::class, 'schedules']);
Route::get('/schedules/create', [AppController::class, 'create']);
Route::get('/schedules/{id}', [AppController::class, 'show']);

Route::post('/schedules', [AppController::class, 'store']);
Route::post('/schedules/{id}', [AppController::class, 'updateMonth']);

Route::post('/trains', [AppController::class, 'storeTrain']);
