<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\City;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         | Navigation-wide data is normally shared by the SetLocale and
         | ShareSiteData middleware. An exception raised before those run
         | (e.g. a 404 during route model binding) renders the error views
         | without them, so provide the same data as a fallback the first
         | time any view renders on such a request.
         */
        View::composer('*', function (ViewInstance $view): void {
            $factory = $view->getFactory();

            if (array_key_exists('announcements', $factory->getShared())) {
                return; // SetLocale + ShareSiteData already ran for this request.
            }

            $locale = $this->fallbackLocale();

            try {
                $factory->share([
                    'currentLocale' => $locale,
                    'cities' => City::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
                    'announcements' => Announcement::query()->active()->orderBy('sort_order')->get(),
                    'currentCity' => selected_city(),
                ]);
            } catch (\Throwable) {
                // Never let the fallback itself break error rendering.
                $factory->share([
                    'currentLocale' => $locale,
                    'cities' => collect(),
                    'announcements' => collect(),
                    'currentCity' => null,
                ]);
            }

            App::setLocale($locale);
        });
    }

    /**
     * The language to fall back to when SetLocale has not run yet.
     */
    private function fallbackLocale(): string
    {
        try {
            $locale = session('locale')
                ?? auth()->user()?->preferred_language
                ?? config('app.locale');
        } catch (\Throwable) {
            $locale = config('app.locale');
        }

        return in_array($locale, ['en', 'np'], true) ? $locale : config('app.locale');
    }
}
