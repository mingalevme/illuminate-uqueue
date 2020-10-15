<?php

namespace Mingalevme\Illuminate\UQueue;

use Mingalevme\Illuminate\UQueue\Jobs\Uniqueable;

class RedisQueue extends \Illuminate\Queue\RedisQueue
{
    /**
     * Get the size of the queue.
     *
     * @param string|null $queue
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
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queue = $this->getQueue($queue);

        $this->getConnection()->eval(
            LuaScripts::push(), 2,
                $queue, // KEY1
                $queue.':notify', // KEY2
                microtime(true), // ARGV1
                $payload // ARGV2
        );

        return json_decode($payload, true)['id'] ?? null;
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createPayloadArray($job, $queue, $data = '')
    {
        $payload = parent::createPayloadArray($job, $queue, $data);

        if (is_object($job) && $job instanceof Uniqueable) {
            $payload['id'] = $job->uniqueable();
            if (!empty($payload['uuid'])) {
                $payload['uuid'] = $this->uuid($payload['id']);
            }
        }
        
        return $payload;
    }
    
    /**
     * Migrate the delayed jobs that are ready to the regular queue.
     *
     * @param  string  $from
     * @param  string  $to
     * @return array
     */
    public function migrateExpiredJobs($from, $to)
    {
        return $this->getConnection()->eval(
            LuaScripts::migrateExpiredJobs(), 3,
                $from, // KEY1
                $to, // KEY2
                $to.':notify', // KEY3
                $this->currentTime()  // ARGV1
        );
    }

    /**
     * Retrieve the next job from the queue.
     *
     * @param  string  $queue
     * @param  bool  $block
     * @return array
     */
    protected function retrieveNextJob($queue, $block = true)
    {
        $nextJob = $this->getConnection()->eval(
            LuaScripts::pop(), 3,
            $queue, // KEYS1
            $queue.':reserved', // KEYS2
            $queue.':notify', // KEYS3
            $this->availableAt($this->retryAfter) // ARGV1
        );

        if (empty($nextJob)) {
            return [null, null];
        }

        [$job, $reserved] = $nextJob;

        if (! $job && ! is_null($this->blockFor) && $block &&
            $this->getConnection()->blpop([$queue.':notify'], $this->blockFor)) {
            return $this->retrieveNextJob($queue, false);
        }

        return [$job, $reserved];
    }

    protected function uuid(string $seed): string
    {
        $data = substr(sha1($seed), -16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
