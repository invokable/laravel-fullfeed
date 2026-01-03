<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Revolution\Fullfeed\Extractor\RemoveElements;
use Revolution\Fullfeed\Extractor\ReplaceMatches;
use Revolution\Fullfeed\Extractor\StripTags;
use Revolution\Fullfeed\Facades\FullFeed;

it('can fetch full feed content', function () {
    Http::fake([
        'https://laravel.com/blog/test' => Http::response('<html><body><article><h1>Full Article</h1><p>This is the full content of the article.</p></article></body></html>', 200),
    ]);

    $content = FullFeed::get('https://laravel.com/blog/test');

    expect($content)->toContain('Full Article')
        ->and($content)->toContain('This is the full content of the article.');
});

it('returns empty for non-existing rules', function () {
    Http::fake([
        'https://example.com/no-rules' => Http::response('<html><body><p>No rules here.</p></body></html>', 200),
    ]);

    $content = FullFeed::get('https://example.com/no-rules');

    expect($content)->toBeEmpty();
});

it('returns empty for failed HTTP requests', function () {
    Http::fake([
        'https://example.com/fail' => Http::response('', 404),
    ]);

    $content = FullFeed::get('https://example.com/fail');

    expect($content)->toBeEmpty();
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

it('can merge new rules into existing ones', function () {
    $initialCount = FullFeed::collect()->count();

    $newRules = [
        [
            'name' => 'example.com',
            'data' => [
                'url' => '^https://example.com/new-article',
                'selector' => 'article',
            ],
        ],
    ];

    FullFeed::merge($newRules);

    $newCount = FullFeed::collect()->count();

    expect($newCount)->toBe($initialCount + 1)
        ->and(FullFeed::has('https://example.com/new-article'))->toBeTrue();
});

it('can use xpath extractor', function () {
    Http::fake([
        'https://example.com/new-article' => Http::response('<html><article><h1>Mocked Article</h1><p>This is mocked content.</p></article></html>', 200),
    ]);

    $newRules = [
        [
            'name' => 'example.com',
            'data' => [
                'url' => '^https://example.com/new-article',
                'xpath' => '//article',
            ],
        ],
    ];

    FullFeed::merge($newRules);

    $content = FullFeed::get('https://example.com/new-article');

    expect($content)->toContain('Mocked Article')
        ->and($content)->toContain('This is mocked content.');
});

it('can use remove elements extractor', function () {
    Http::fake([
        'https://example.com/new-article' => Http::response('<html><article><h1>Mocked Article</h1><p id="1">This is mocked content 1.</p><p id="2">This is mocked content 2.</p><div id="3">This is mocked content 3.</div></article></html>', 200),
    ]);

    $newRules = [
        [
            'name' => 'example.com',
            'data' => [
                'url' => '^https://example.com/new-article',
                'callable' => [RemoveElements::class],
                'remove' => ['h1', 'p#1', 'div#3'],
            ],
        ],
    ];

    FullFeed::merge($newRules);

    $content = FullFeed::get('https://example.com/new-article');

    expect($content)->not->toContain('Mocked Article')
        ->and($content)->not->toContain('This is mocked content 1.')
        ->and($content)->toContain('This is mocked content 2.')
        ->and($content)->not->toContain('This is mocked content 3.');
});

it('can use replace extractor', function () {
    $html = '<html><article><h1>Mocked Article</h1><p>This is mocked content.</p></article></html>';

    $newRules = [
        [
            'name' => 'example.com',
            'data' => [
                'url' => '^https://example.com/new-article',
                'selector' => 'article',
                'callable' => [ReplaceMatches::class],
                'replace' => [
                    [
                        'pattern' => '/<h1>.*?<\/h1>/s',
                        'replace' => '<h1>Replaced Title</h1>',
                    ],
                ],
            ],
        ],
    ];

    FullFeed::merge($newRules);

    $content = FullFeed::extract(source: $html, url: 'https://example.com/new-article');

    expect($content)->not->toContain('Mocked Article')
        ->and($content)->toContain('Replaced Title')
        ->and($content)->toContain('This is mocked content.');
});

it('can use strip tags extractor', function () {
    $html = '<html><article><h1>Mocked Article</h1><p>This is mocked content.</p></article></html>';

    $newRules = [
        [
            'name' => 'example.com',
            'data' => [
                'url' => '^https://example.com/new-article',
                'callable' => [StripTags::class.':h1,p'],
            ],
        ],
    ];

    FullFeed::merge($newRules);

    $content = FullFeed::extract(source: $html, url: 'https://example.com/new-article');

    expect($content)->not->toContain('<article>')
        ->and($content)->toContain('<h1>Mocked Article</h1>')
        ->and($content)->toContain('<p>This is mocked content.</p>');
});
