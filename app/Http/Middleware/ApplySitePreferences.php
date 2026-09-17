<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplySitePreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = SiteSetting::value('timezone', config('app.timezone'));
        $locale = SiteSetting::value('locale', config('app.locale'));

        config(['app.timezone' => $timezone, 'app.locale' => $locale]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
