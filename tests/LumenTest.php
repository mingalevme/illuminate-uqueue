<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Redis\RedisServiceProvider;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;
use Mingalevme\Illuminate\UQueue\LumenUQueueServiceProvider;

class LumenTest extends LumenTestCase
{
    use PackageTest;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        require_once __DIR__ . '/../vendor/laravel/lumen/app/Console/Kernel.php';

        $app = new \Laravel\Lumen\Application(
            realpath(__DIR__ . '/../vendor/laravel/lumen')
        );

        $app->bind('path.storage', function () {
            return '/tmp';
        });

        $app->alias('db', 'Illuminate\Database\ConnectionResolverInterface');

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Laravel\Lumen\Exceptions\Handler::class
        );
        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Laravel\Lumen\Console\Kernel::class
        );

        $app->withFacades();

        $app->register(RedisServiceProvider::class);

        $app->register(LumenUQueueServiceProvider::class);

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        (new \JobsAddUniqueable())->down();
        (new \CreateJobsTable())->down();

        (new \CreateJobsTable())->up();
        (new \JobsAddUniqueable())->up();
    }

    public function tearDown()
    {
        (new \JobsAddUniqueable())->down();
        (new \CreateJobsTable())->down();

        parent::tearDown();
    }
}
