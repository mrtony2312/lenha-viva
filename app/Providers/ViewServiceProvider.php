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
            $categories = [];

            if (config()->has('loja_products')) {
                $allProducts = collect(config('loja_products'));
                $categories = $allProducts
                    ->pluck('category')
                    ->unique()
                    ->filter()
                    ->mapWithKeys(function ($category) {
                        return [$category => CategoryLabels::label($category)];
                    })
                    ->sort()
                    ->toArray();
            }

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
            $view->with('topbarMessages', $topbarMessages);
        });
    }
}
