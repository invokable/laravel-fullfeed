<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;

/**
 * Example of a custom extractor.
 */
class TogetterExtractor
{
    /**
     * @param  string  $data  Target html content
     * @param  string  $url  Original URL
     * @param  array  $rule  Extraction rule
     */
    public function __invoke(string $data, string $url, array $rule): string
    {
        $selector = data_get($rule, 'data.selector');

        $html = HTMLDocument::createFromString(
            source: $data,
            options: LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS,
            overrideEncoding: 'UTF-8',
        );

        $nodes = $html->querySelectorAll($selector);

        if ($nodes->length > 0) {
            // Remove unwanted elements.
            // Images are lazy-loaded by JS and show nothing, so remove them.
            // 画像はJSで遅延ロードしていて何も表示されないので削除。
            $unwantedSelectors = data_get($rule, 'data.remove', []);
            foreach ($nodes as $node) {
                foreach ($unwantedSelectors as $unwantedSelector) {
                    $unwantedNodes = $node->querySelectorAll($unwantedSelector);
                    foreach ($unwantedNodes as $unwantedNode) {
                        $unwantedNode->parentNode->removeChild($unwantedNode);
                    }
                }
            }

            return $html->saveHtml($nodes->item(0));
        }

        return '';
    }
}
