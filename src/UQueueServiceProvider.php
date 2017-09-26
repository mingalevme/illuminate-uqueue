<?php

namespace Mingalevme\Illuminate\UQueue;

use Mingalevme\Illuminate\UQueue\Connectors\RedisConnector;
use Mingalevme\Illuminate\UQueue\Connectors\DatabaseConnector;

class UQueueServiceProvider extends \Illuminate\Queue\QueueServiceProvider
{
    /**
     * Register the queue manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        unset($this->app->availableBindings['queue']);
        unset($this->app->availableBindings['queue.connection']);
        unset($this->app->availableBindings['Illuminate\Contracts\Queue\Factory']);
        unset($this->app->availableBindings['Illuminate\Contracts\Queue\Queue']);
        
        $this->app->alias('queue', 'queue.connection');
        $this->app->alias('queue', 'Illuminate\Contracts\Queue\Factory');
        $this->app->alias('queue', 'Illuminate\Contracts\Queue\Queue');
        
        $this->app->bind(\Illuminate\Queue\Connectors\RedisConnector::class, RedisConnector::class);
        $this->app->bind(\Illuminate\Queue\Connectors\DatabaseConnector::class, DatabaseConnector::class);
        
        $this->app->configure('queue');
        
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
            return $this->app->make(\Illuminate\Queue\Connectors\RedisConnector::class, [
                $this->app['redis'],
            ]);
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
            return $this->app->make(\Illuminate\Queue\Connectors\DatabaseConnector::class, [
                $this->app['db'],
            ]);
        });
    }
}
