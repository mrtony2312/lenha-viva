<?php

namespace App\Providers;

use App\Support\CategoryLabels;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $categories = CategoryLabels::forNavigation(false);
            $navCategories = CategoryLabels::forNavigation(true);

            $topbarMessages = collect(config('company.topbar_messages', []))
                ->map(function ($item) {
                    if (is_string($item)) {
                        return ['text' => $item, 'url' => null];
                    }

                    $url = null;
                    $routeName = $item['route'] ?? null;
                    if (is_string($routeName) && $routeName !== '' && Route::has($routeName)) {
                        $url = route($routeName);
                    } elseif (! empty($item['url'])) {
                        $url = $item['url'];
                    }

                    return [
                        'text' => (string) ($item['text'] ?? ''),
                        'url' => $url,
                    ];
                })
                ->filter(fn ($item) => $item['text'] !== '')
                ->values()
                ->all();

            $view->with('categories', $categories);
            $view->with('navCategories', $navCategories);
            $view->with('topbarMessages', $topbarMessages);
        });
    }
}
