<?php

namespace Mingalevme\Illuminate\UQueue;

use Illuminate\Queue\QueueServiceProvider;
use Mingalevme\Illuminate\UQueue\Connectors\RedisConnector;
use Mingalevme\Illuminate\UQueue\Connectors\DatabaseConnector;

class LaravelUQueueServiceProvider extends QueueServiceProvider
{
    /**
     * Register the queue manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        unset($this->app['queue']);
        unset($this->app['queue.connection']);
        unset($this->app['Illuminate\Contracts\Queue\Factory']);
        unset($this->app['Illuminate\Contracts\Queue\Queue']);

        $this->app->alias('queue', 'queue.connection');
        $this->app->alias('queue', 'Illuminate\Contracts\Queue\Factory');
        $this->app->alias('queue', 'Illuminate\Contracts\Queue\Queue');

        $this->app->alias('redis', 'Illuminate\Contracts\Redis\Factory');

        $this->app->bind(\Illuminate\Queue\Connectors\RedisConnector::class, RedisConnector::class);
        $this->app->bind(\Illuminate\Queue\Connectors\DatabaseConnector::class, DatabaseConnector::class);

        /*$this->app->configure('queue');

        $this->publishes([
            __DIR__ . '/../config/lock.php'
            => $this->app->basePath() . '/config/lock.php',
        ], 'config');*/

        parent::registerManager();
    }

    /**
     * Register the Redis queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerRedisConnector($manager)
    {
        $manager->addConnector('redis', function () {
            return new RedisConnector($this->app['db']);
        });
    }

    /**
     * Register the database queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerDatabaseConnector($manager)
    {
        $manager->addConnector('database', function () {
            return new DatabaseConnector($this->app['db']);
        });
    }
}
