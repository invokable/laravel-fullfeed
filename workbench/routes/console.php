<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Fullfeed\Facades\FullFeed;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

Artisan::command('full', function () {
    dump(FullFeed::first('https://laravel.com/blog/how-we-built-laravel-wrapped'));
    $this->info(FullFeed::get('https://laravel.com/blog/how-we-built-laravel-wrapped'));
});
