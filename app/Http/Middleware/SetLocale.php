<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the visitor's chosen language (session, then account preference).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale')
            ?? $request->user()?->preferred_language
            ?? config('app.locale');

        if (! in_array($locale, ['en', 'np'], true)) {
            $locale = 'en';
        }

        App::setLocale($locale);
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
