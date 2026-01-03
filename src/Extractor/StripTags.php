<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Illuminate\Support\Str;
use Revolution\Fullfeed\Context;

/**
 * Strip HTML tags from the source.
 */
class StripTags
{
    public function __invoke(Context $context, Closure $next, string ...$allowedTags): Context
    {
        $context->source = Str::of($context->source)->stripTags($allowedTags)->trim()->toString();

        return $next($context);
    }
}
