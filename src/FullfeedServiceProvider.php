<?php

declare(strict_types=1);

namespace Revolution\Fullfeed;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class FullfeedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/fullfeed.php', 'fullfeed');

        $this->app->scoped(FullFeedClient::class, function () {
            $paths = config('fullfeed.paths', []);
            $items = [];
            foreach ($paths as $path) {
                $items = rescue(fn () => array_merge($items, File::json($path)), $items, report: true);
            }

            return new FullFeedClient($items);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fullfeed.php' => config_path('fullfeed.php'),
            ], 'fullfeed');

            $this->publishes([
                __DIR__.'/../resources/fullfeed' => resource_path('fullfeed'),
            ], 'fullfeed');
        }
    }
}
