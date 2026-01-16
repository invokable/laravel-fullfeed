<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Illuminate\Support\Str;
use Revolution\Fullfeed\Context;

/**
 * Remove all extra whitespace from the source.
 */
class Squish
{
    public function __invoke(Context $context, Closure $next, string ...$allowedTags): Context
    {
        $context->source = Str::squish($context->source);

        return $next($context);
    }
}
