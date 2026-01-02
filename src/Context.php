<?php

declare(strict_types=1);

namespace Revolution\Fullfeed;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;

class Context
{
    use Conditionable;
    use Tappable;

    /**
     * @param  string  $source  Target html content
     * @param  string  $url  Original URL
     * @param  array  $rule  Extraction rule
     */
    public function __construct(
        public string $source,
        public readonly string $url,
        public readonly array $rule,
    ) {
        $encoding = data_get($rule, 'data.enc', 'UTF-8');
        if (blank($encoding)) {
            $encoding = 'UTF-8';
        }
        if ($encoding !== 'UTF-8') {
            $this->source = mb_convert_encoding($this->source, 'UTF-8', $encoding);
        }
    }

    public function htmlDocument(): HTMLDocument
    {
        return HTMLDocument::createFromString(
            source: $this->source,
            options: LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS,
            overrideEncoding: 'UTF-8',
        );
    }
}
