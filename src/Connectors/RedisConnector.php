<?php

namespace Mingalevme\Illuminate\UQueue\Connectors;

use Illuminate\Contracts\Queue\Queue;
use Mingalevme\Illuminate\UQueue\RedisQueue;

class RedisConnector extends \Illuminate\Queue\Connectors\RedisConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return Queue
     */
    public function connect(array $config)
    {
        return new RedisQueue(
            $this->redis, $config['queue'],
            $config['connection'] ?? $this->connection,
            $config['retry_after'] ?? 60,
            $config['block_for'] ?? null
        );
    }
}
