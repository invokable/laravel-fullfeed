<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Dom\Element;
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

        /** @var ?Element $node */
        $node = rescue(fn () => $html->querySelector($selector), report: false);

        if (! is_null($node)) {
            $context->source = $html->saveHtml($node);

            return $next($context);
        }

        return $next($context);
    }
}
