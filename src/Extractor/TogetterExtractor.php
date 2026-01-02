<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Revolution\Fullfeed\Context;

/**
 * Example of a custom extractor.
 */
class TogetterExtractor
{
    public function __invoke(Context $context, Closure $next): Context
    {
        $selector = data_get($context->rule, 'data.selector', 'section.entry_main');

        $html = $context->htmlDocument();

        $nodes = $html->querySelectorAll($selector);

        if ($nodes->length > 0) {
            // Remove unwanted elements.
            // Images are lazy-loaded by JS and show nothing, so remove them.
            // 画像はJSで遅延ロードしていて何も表示されないので削除。
            $unwantedSelectors = data_get($context->rule, 'data.remove', []);
            foreach ($nodes as $node) {
                foreach ($unwantedSelectors as $unwantedSelector) {
                    $unwantedNodes = $node->querySelectorAll($unwantedSelector);
                    foreach ($unwantedNodes as $unwantedNode) {
                        $unwantedNode->parentNode->removeChild($unwantedNode);
                    }
                }
            }

            $context->source = $html->saveHtml($nodes->item(0));

            return $next($context);
        }

        return $next($context);
    }
}
