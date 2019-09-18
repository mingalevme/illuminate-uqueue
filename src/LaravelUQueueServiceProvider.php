<?php

namespace Mingalevme\Illuminate\UQueue;

use Illuminate\Contracts\Queue\Factory;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
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
        unset($this->app[Factory::class]);
        unset($this->app[Queue::class]);

        $this->app->alias('queue', 'queue.connection');
        $this->app->alias('queue', Factory::class);
        $this->app->alias('queue', Queue::class);

        $this->app->alias('redis', RedisFactory::class);

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
