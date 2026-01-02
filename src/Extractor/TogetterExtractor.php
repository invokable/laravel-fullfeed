<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Dom\Element;
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

        /** @var ?Element $node */
        $node = rescue(fn () => $html->querySelector($selector), report: false);

        if (is_null($node)) {
            return $next($context);
        }

        // Remove unwanted elements.
        // Images are lazy-loaded by JS and show nothing, so remove them.
        // 画像はJSで遅延ロードしていて何も表示されないので削除。
        $unwantedSelectors = data_get($context->rule, 'data.remove', []);

        foreach ($unwantedSelectors as $unwantedSelector) {
            $unwantedNodes = $node->querySelectorAll($unwantedSelector);
            foreach ($unwantedNodes as $unwantedNode) {
                $unwantedNode->parentNode->removeChild($unwantedNode);
            }
        }

        $context->source = $html->saveHtml($node);

        return $next($context);
    }
}
