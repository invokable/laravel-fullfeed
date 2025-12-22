<?php

declare(strict_types=1);

namespace Revolution\Fullfeed;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FullFeedClient
{
    public function __construct(
        private readonly array $items = [],
    ) {}

    /**
     * Get the content of the URL and return the part specified by data.selector or data.xpath.
     *
     * URLのコンテンツを取得し、data.selectorやdata.xpathで指定された部分を返す
     */
    public function get(string $url): ?string
    {
        $response = Http::when(
            filled(config('fullfeed.user_agent')),
            fn ($client) => $client->withUserAgent(config('fullfeed.user_agent')))
            ->get($url);

        if ($response->failed()) {
            return null;
        }

        $body = $response->body();

        return $this->extract($body, $url);
    }

    /**
     * Extract the part specified by data.selector or data.xpath from the given HTML data.
     *
     * 与えられたHTMLデータから、data.selectorやdata.xpathで指定された部分を抽出する
     */
    public function extract(string $data, string $url): ?string
    {
        $rule = $this->first($url);
        if (blank($rule)) {
            return null;
        }

        $selector = data_get($rule, 'data.selector');
        $xpath = data_get($rule, 'data.xpath');
        $encoding = data_get($rule, 'data.enc', 'UTF-8');
        if (blank($encoding)) {
            $encoding = 'UTF-8';
        }

        if ($encoding !== 'UTF-8') {
            $data = mb_convert_encoding($data, 'UTF-8', $encoding);
        }

        $html = HTMLDocument::createFromString(
            source: $data,
            options: LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS,
            overrideEncoding: 'UTF-8',
        );

        if (filled($selector)) {
            $nodes = $html->querySelectorAll($selector);
        } elseif (filled($xpath)) {
            $nodes = new XPath($html)->query($xpath);
        } else {
            return null;
        }

        if ($nodes->length > 0) {
            return $html->saveHtml($nodes->item(0));
        }

        return null;
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
