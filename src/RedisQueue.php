<?php

namespace Mingalevme\Illuminate\UQueue;

use Mingalevme\Illuminate\UQueue\LuaScripts;
use Mingalevme\Illuminate\UQueue\Jobs\Uniqueable;

class RedisQueue extends \Illuminate\Queue\RedisQueue
{
    /**
     * Get the size of the queue.
     *
     * @param  string  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $queue = $this->getQueue($queue);

        return $this->getConnection()->eval(
            LuaScripts::size(), 3, $queue, $queue.':delayed', $queue.':reserved'
        );
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->getConnection()->zadd($this->getQueue($queue), 'NX', microtime(true), $payload);

        return json_decode($payload, true)['id'] ?? null;
    }
    
    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @return string
     */
    protected function createPayloadArray($job, $data = '')
    {
        $payload = parent::createPayloadArray($job, $data);
        
        if (is_object($job) && $job instanceof Uniqueable) {
            $payload['id'] = $job->uniqueable();
        }
        
        return $payload;
    }
    
    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param  string  $from ZSET key
     * @param  string  $to   ZSET key
     * @return array
     */
    public function migrateExpiredJobs($from, $to)
    {
        return $this->getConnection()->eval(
            LuaScripts::migrateExpiredJobs(), 2, $from, $to, $this->currentTime()
        );
    }

    /**
     * Retrieve the next job from the queue.
     *
     * @param  string  $queue
     * @return array
     */
    protected function retrieveNextJob($queue)
    {
        return $this->getConnection()->eval(
            LuaScripts::pop(), 2, $queue, $queue.':reserved',
            $this->availableAt($this->retryAfter)
        );
    }
}
