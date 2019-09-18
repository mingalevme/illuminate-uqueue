<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Mingalevme\Illuminate\UQueue\LaravelUQueueServiceProvider;

class LaravelTest extends LaravelTestCase
{
    use PackageTest;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        require_once __DIR__ . '/../vendor/laravel/laravel/app/Console/Kernel.php';
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app->register(RedisServiceProvider::class);
        $app->register(LaravelUQueueServiceProvider::class);
        $app->alias('db', 'Illuminate\Database\ConnectionResolverInterface');
        return $app;
    }
}
