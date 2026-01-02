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
        collect($replaces)->each(function (array $replace) use ($context) {
            $pattern = data_get($replace, 'pattern');
            $replace = data_get($replace, 'replace', '');

            if (blank($pattern)) {
                return;
            }

            $context->source = Str::replaceMatches($pattern, $replace, $context->source);
        });

        return $next($context);
    }
}
