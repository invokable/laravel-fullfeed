<?php

declare(strict_types=1);

namespace Revolution\Fullfeed;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Str;
use Revolution\Fullfeed\Extractor\SelectorExtractor;
use Revolution\Fullfeed\Extractor\XPathExtractor;

class FullFeedClient
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * Get the content of the URL and return the part specified by data.selector or data.xpath.
     *
     * URLのコンテンツを取得し、data.selectorやdata.xpathで指定された部分を返す
     */
    public function get(string $url): string
    {
        $response = Http::when(
            filled(config('fullfeed.user_agent')),
            fn ($client) => $client->withUserAgent(config('fullfeed.user_agent')),
        )->get($url);

        if ($response->failed()) {
            return '';
        }

        return $this->extract($response->body() ?? '', $url);
    }

    /**
     * Extract the part specified by data.selector or data.xpath from the given HTML data.
     *
     * 与えられたHTMLデータから、data.selectorやdata.xpathで指定された部分を抽出する
     */
    public function extract(string $source, string $url): string
    {
        $rule = $this->first($url);
        if (blank($rule)) {
            return '';
        }

        $context = new Context(
            source: $source,
            url: $url,
            rule: $rule,
        );

        $pipes = Collection::wrap(data_get($rule, 'data.callable'))
            ->push(XPathExtractor::class)
            ->push(SelectorExtractor::class)
            ->toArray();

        // If extraction result is same as source, consider it as failed extraction
        return Pipeline::send($context)
            ->through($pipes)
            ->thenReturn()
            ->when(
                $context->source === $source,
                fn () => $context->tap(fn ($context) => $context->source = ''),
            )->source;
    }

    /**
     * Ensure there is a rule corresponding to the URL using data.url regex.
     *
     * data.urlの正規表現を使って、URLに対応するルールが存在するか確認する
     */
    public function has(string $url): bool
    {
        return array_any($this->items, fn ($item) => Str::isMatch('@'.data_get($item, 'data.url').'@i', $url));
    }

    /**
     * Get first rule data corresponding to the URL.
     *
     * URLに対応する最初のルールデータを取得
     */
    public function first(string $url): ?array
    {
        return array_find($this->items, fn ($item) => Str::isMatch('@'.data_get($item, 'data.url').'@i', $url));
    }

    /**
     * Merge new items to the beginning of the existing items.
     */
    public function merge(array $items): static
    {
        $this->items = array_merge($items, $this->items);

        return $this;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function collect(): Collection
    {
        return new Collection($this->items);
    }
}
