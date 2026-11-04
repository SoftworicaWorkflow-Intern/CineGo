<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use App\Models\City;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareSiteData
{
    /**
     * Make navigation-wide data available to every view (cities for the
     * selector, announcement bar items, chosen city).
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cities = City::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $announcements = Announcement::query()
            ->active()
            ->orderBy('sort_order')
            ->get();

        View::share('cities', $cities);
        View::share('announcements', $announcements);
        View::share('currentCity', selected_city());

        return $next($request);
    }
}
