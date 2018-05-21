<?php

namespace Mingalevme\Illuminate\UQueue;

use Illuminate\Queue\Connectors\DatabaseConnector as IlluminateDatabaseConnector;
use Illuminate\Queue\Connectors\RedisConnector as IlluminateRedisConnector;
use Illuminate\Queue\QueueServiceProvider;
use Mingalevme\Illuminate\UQueue\Connectors\RedisConnector as UQueueRedisConnector;
use Mingalevme\Illuminate\UQueue\Connectors\DatabaseConnector as UQueueDatabaseConnector;

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

        $this->app->bind(IlluminateRedisConnector::class, UQueueRedisConnector::class);
        $this->app->bind(IlluminateDatabaseConnector::class, UQueueDatabaseConnector::class);

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
            return new UQueueRedisConnector($this->app['redis']);
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
            return new UQueueDatabaseConnector($this->app['db']);
        });
    }
}
