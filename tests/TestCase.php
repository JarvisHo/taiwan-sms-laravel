<?php

namespace Jarvisho\TaiwanSmsLaravel\Tests;

use Jarvisho\TaiwanSmsLaravel\TaiwanSmsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            TaiwanSmsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
    }
}
