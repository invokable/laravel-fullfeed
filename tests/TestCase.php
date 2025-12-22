<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Revolution\Fullfeed\FullfeedServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FullfeedServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        //
    }
}
