<?php

namespace Mingalevme\Illuminate\UQueue\Connectors;

use Mingalevme\Illuminate\UQueue\DatabaseQueue;

class DatabaseConnector extends \Illuminate\Queue\Connectors\DatabaseConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new DatabaseQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );
    }
}
