<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Mingalevme\Illuminate\UQueue\LaravelUQueueServiceProvider;

class LaravelTest extends LaravelTestCase
{
    use PackageTest;

    /**
     * @return Application
     */
    public function createApplication()
    {
        require_once __DIR__ . '/../vendor/laravel/laravel/app/Console/Kernel.php';
        /** @var Application $app */
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $app->instance(\Redis::class, null);
        $app->forgetInstance(\Redis::class);
        $app->make('config')->set(['app.aliases.Redis' => null]);
        $app->register(RedisServiceProvider::class);
        $app->register(LaravelUQueueServiceProvider::class);
        return $app;
    }
}
