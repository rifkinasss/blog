<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\ApplySitePreferences;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\SetLocalizedLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([AddRequestId::class, AddSecurityHeaders::class]);
        if ($trustedProxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: array_filter(array_map('trim', explode(',', $trustedProxies))));
        }
        $middleware->web(append: [
            ApplySitePreferences::class,
            AddLinkHeadersForPreloadedAssets::class,
            AuthenticateSession::class,
        ]);
        $middleware->alias([
            'api-token' => AuthenticateApiToken::class,
            'set-localized-locale' => SetLocalizedLocale::class,
            'localize' => LaravelLocalizationRoutes::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->respond(function (Response $response): Response {
            $response->headers->set('X-Request-ID', request()->attributes->get('request_id', (string) str()->uuid()));

            return $response;
        });
    })->create();
