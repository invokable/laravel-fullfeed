<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Fullfeed\Facades\FullFeed;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

// vendor/bin/testbench vendor:publish --tag=fullfeed --forceでvendor内のファイルを更新してからテスト用コマンドを実行。
Artisan::command('full', function () {
    //dump(FullFeed::all());
    $this->info(FullFeed::get('https://laravel.com/blog/how-we-built-laravel-wrapped'));
});
