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
        'https://example.com/new-article' => Http::response('<html></html><article><h1>Mocked Article</h1><p>This is mocked content.</p></article></html>', 200),
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

it('can use custom extractor', function () {
    $html = <<<'HTML'
<section class="entry_main tweet_box">
                    <div class='list_box type_tweet impl_profile' data-index='0' data-id='1111'>
            <span>
                <a class="user_link"
                   rel="noreferrer"
                   href='https://x.com/****'
                   target='_blank'>
                            <img class="lzpk " data-s="2" src="https://s.tgstc.com/static/web/p.gif" data-e="3" />
                                                    <strong class="emj">name</strong>
                                        <span class="status_name">@user</span>
                </a>
            </span>
            <p class='tweet emj'><span class="f20 c01">ツイート

<a href="https://x.com/search?q=****" target="_blank" rel="noreferrer">#***</a> <a href="https://t.co/***" target="_blank" rel="noreferrer">pic.x.com/***</a></span></p>
            <span class='status'>
                <span class="intent"></span>
                <span>
                    <a class="link" href="https://x.com/mn_enta_tv/status/****" target="_blank" rel="noreferrer">2026-01-01 00:00:00</a>
                </span>
            </span>
                                <div class="list_photo_box">
                <figure class='list_photo'>
                    <img class="lzpk " data-s="4" src="https://s.tgstc.com/static/web/p.gif" data-e="5"/>
                </figure>
                </div>
                </div>
</section>
HTML;

    Http::fake([
        'https://togetter.com/li/1' => Http::response($html, 200),
    ]);

    $content = FullFeed::get('https://togetter.com/li/1');

    expect($content)->toContain('ツイート')
        ->and($content)->not->toContain('img');
});
