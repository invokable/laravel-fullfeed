<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Fullfeed\Facades\FullFeed;

it('can fetch full feed content', function () {
    Http::fake([
        'https://laravel.com/blog/test' => Http::response('<html><body><article><h1>Full Article</h1><p>This is the full content of the article.</p></article></body></html>', 200),
    ]);

    $content = FullFeed::get('https://laravel.com/blog/test');

    expect($content)->toContain('Full Article')
        ->and($content)->toContain('This is the full content of the article.');
});
