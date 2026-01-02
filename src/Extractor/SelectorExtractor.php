<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Revolution\Fullfeed\Context;

/**
 * CSS Selector based extractor.
 */
class SelectorExtractor
{
    public function __invoke(Context $context, Closure $next): Context
    {
        $selector = data_get($context->rule, 'data.selector');
        if (blank($selector)) {
            return $next($context);
        }

        $html = $context->htmlDocument();

        $nodes = $html->querySelectorAll($selector);

        if ($nodes->length > 0) {
            $context->source = $html->saveHtml($nodes->item(0));

            return $next($context);
        }

        return $next($context);
    }
}
