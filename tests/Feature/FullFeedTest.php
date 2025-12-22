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

it('returns null for non-existing rules', function () {
    Http::fake([
        'https://example.com/no-rules' => Http::response('<html><body><p>No rules here.</p></body></html>', 200),
    ]);

    $content = FullFeed::get('https://example.com/no-rules');

    expect($content)->toBeNull();
});

it('returns null for failed HTTP requests', function () {
    Http::fake([
        'https://example.com/fail' => Http::response('', 404),
    ]);

    $content = FullFeed::get('https://example.com/fail');

    expect($content)->toBeNull();
});

it('can check existence of rules for a URL', function () {
    $hasRule = FullFeed::has('https://laravel.com/blog/test');
    $noRule = FullFeed::has('https://example.com/no-rules');

    expect($hasRule)->toBeTrue()
        ->and($noRule)->toBeFalse();
});

it('can retrieve all rules and convert to array/collection', function () {
    $allRules = FullFeed::all();
    $rulesArray = FullFeed::toArray();
    $rulesCollection = FullFeed::collect();

    expect($allRules)->toBeArray()
        ->and($rulesArray)->toBeArray()
        ->and($rulesCollection)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and(count($allRules))->toBeGreaterThan(0)
        ->and(count($rulesArray))->toBeGreaterThan(0)
        ->and($rulesCollection->count())->toBeGreaterThan(0);
});

it('can mock FullFeed responses', function () {
    FullFeed::expects('get')
        ->with('https://laravel.com/blog/mock-test')
        ->andReturn('<article><h1>Mocked Article</h1><p>This is mocked content.</p></article>');

    $content = FullFeed::get('https://laravel.com/blog/mock-test');

    expect($content)->toContain('Mocked Article')
        ->and($content)->toContain('This is mocked content.');
});
