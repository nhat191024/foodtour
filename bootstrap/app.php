<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*') && ! app()->runningInConsole()) {
                $status = $e instanceof HttpException ? $e->getStatusCode() : 500;

                if (in_array($status, [401, 403, 404, 500, 503])) {
                    return Inertia::render('error/Show', [
                        'status' => $status,
                    ])->toResponse($request)->setStatusCode($status);
                }
            }

            return null;
        });

        $exceptions->renderable(function (TooManyRequestsHttpException $e, $request) {
            if ($request->header('X-Inertia')) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? 'vài';
                $message = "Bạn tác quá nhanh. Vui lòng đợi {$retryAfter} giây rồi bấm lại.";
                return back()->with('error', $message);
            }
        });

    })->create();
