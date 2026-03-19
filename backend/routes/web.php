<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// === ЗАГЛУШКА ДЛЯ АВТОРИЗАЦИИ (чтобы не было ошибки "Route [login] not defined") ===
// Этот маршрут нужен только для middleware, он не используется напрямую
Route::get('/login', function () {
    return response()->json(['message' => 'Use /api/login for authentication'], 401);
})->name('login');

// === ОСНОВНОЙ МАРШРУТ ===
Route::get('/', function () {
    return view('welcome');
});