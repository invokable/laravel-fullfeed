<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Revolution\Fullfeed\Context;

/**
 * Remove unwanted elements from the HTML document.
 */
class RemoveElements
{
    public function __invoke(Context $context, Closure $next): Context
    {
        $html = $context->htmlDocument();

        $unwantedSelectors = data_get($context->rule, 'data.remove', []);
        foreach ($unwantedSelectors as $unwantedSelector) {
            $unwantedNodes = $html->querySelectorAll($unwantedSelector);
            foreach ($unwantedNodes as $unwantedNode) {
                $unwantedNode->parentNode->removeChild($unwantedNode);
            }
        }

        $context->source = $html->saveHtml();

        return $next($context);
    }
}
