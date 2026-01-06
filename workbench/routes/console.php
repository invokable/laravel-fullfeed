<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Revolution\Fullfeed\Facades\FullFeed;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

/**
 * Maintenance commands
 */
// vendor/bin/testbench fullfeed:sort
Artisan::command('fullfeed:sort', function () {
    $json = File::json(__DIR__.'/../../resources/fullfeed/plus.json');
    usort($json, fn ($a, $b) => strcmp($a['name'], $b['name']));
    File::put(__DIR__.'/../../resources/fullfeed/plus.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
})->purpose('sort plus.json by name');

/**
 * Local testing commands
 */
// vendor/bin/testbench full
Artisan::command('full', function () {
    dump(FullFeed::first('https://laravel.com/blog/how-we-built-laravel-wrapped'));
    $this->info(FullFeed::get('https://laravel.com/blog/how-we-built-laravel-wrapped'));
});

// vendor/bin/testbench callable
Artisan::command('callable', function () {
    $this->info(FullFeed::get('https://togetter.com/li/'));
});
