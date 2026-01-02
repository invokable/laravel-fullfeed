<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Dom\XPath;
use Revolution\Fullfeed\Context;

class XPathExtractor
{
    public function __invoke(Context $context, Closure $next): Context
    {
        $xpath = data_get($context->rule, 'data.xpath');
        if (blank($xpath)) {
            return $next($context);
        }

        $html = $context->htmlDocument();

        $nodes = new XPath($html)->query($xpath);

        if ($nodes->length > 0) {
            $context->source = $html->saveHtml($nodes->item(0));

            return $next($context);
        }

        return $next($context);
    }
}
