<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
		// Для API-запросов возвращаем JSON вместо редиректа
		$middleware->redirectUsersTo(function ($request) {
			if ($request->expectsJson()) {
				return null; // Не делать редирект
			}
			return '/login'; // Для веб-запросов оставить редирект
		});
	})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
