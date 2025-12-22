<?php

declare(strict_types=1);

namespace Revolution\Fullfeed\Facades;

use Illuminate\Support\Facades\Facade;
use Revolution\Fullfeed\FullFeedClient;

/**
 * @mixin  FullFeedClient
 */
class FullFeed extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FullFeedClient::class;
    }
}
