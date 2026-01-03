<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Extractor;

use Closure;
use Illuminate\Support\Str;
use Revolution\Fullfeed\Context;

/**
 * Replace matches in the source using regular expressions.
 */
class ReplaceMatches
{
    public function __invoke(Context $context, Closure $next): Context
    {
        $replaces = data_get($context->rule, 'data.replace', []);

        foreach ($replaces as $replace) {
            $pattern = data_get($replace, 'pattern');

            if (blank($pattern)) {
                continue;
            }

            $context->source = Str::replaceMatches(
                pattern: $pattern,
                replace: data_get($replace, 'replace', ''),
                subject: $context->source,
            );
        }

        return $next($context);
    }
}
