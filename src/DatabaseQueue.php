<?php

namespace Mingalevme\Illuminate\UQueue;

use Illuminate\Database\QueryException;
use Illuminate\Queue\InvalidPayloadException;
use Mingalevme\Illuminate\UQueue\Jobs\Uniqueable;

class DatabaseQueue extends \Illuminate\Queue\DatabaseQueue
{
    /**
     * Create a payload array from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return array
     */
    protected function createPayloadArray($job, $data = '', $queue = null)
    {
        $payload = parent::createPayloadArray($job, $data, $queue);
        
        if (is_object($job) && $job instanceof Uniqueable) {
            $payload['unique_id'] = $job->uniqueable();
        }
        
        return $payload;
    }

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  array  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        $record = parent::buildDatabaseRecord($queue, $this->jsonize($payload), $availableAt, $attempts);
        
        if (isset($payload['unique_id'])) {
            $record['unique_id'] = $payload['unique_id'];
        } else {
            $record['unique_id'] = \Illuminate\Support\Str::random(32);
        }
        
        return $record;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayloadArray($job, $data));
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
        return $this->pushToDatabase($queue, json_decode($payload, true));
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayloadArray($job, $data), $delay);
    }

    /**
     * Release a reserved job back onto the queue.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @param  int  $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, json_decode($job->payload, true), $delay, $job->attempts);
    }

    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param  string  $queue
     * @param  array   $payload
     * @param  \DateTime|int  $delay
     * @param  int  $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $uniqueId = array_get($payload, 'unique_id');
        
        if (!$uniqueId) {
            return parent::pushToDatabase($queue, $this->jsonize($payload), $delay, $attempts);
        }
        
        while (true) {
            try {
                return $this->database->table($this->table)->insertGetId($this->buildDatabaseRecord(
                    $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
                ));
            } catch (QueryException $e) {
                $this->handleQueryException($e);
            }
            
            $query = $this->database->table($this->table)
                    ->where('unique_id', $uniqueId)
                    ->where('queue', $this->getQueue($queue));
            
            if (count($results = $query->get(['id']))) {
                return $results[0]->id;
            }
        }
    }
    
    protected function handleQueryException(QueryException $e)
    {
        $driver = $this->database->getDriverName();
        $ecode = intval($e->getCode());

        if ($driver === 'pgsql' && in_array($ecode, [23505])) {
            // pass
        } elseif ($driver === 'sqlite' && in_array($ecode, [23000, 19, 2067])) {
            // pass
        } elseif ($driver === 'mysql' && in_array($ecode, [23000, 1062])) {
            // pass
        } elseif ($driver === 'sqlsrv' && in_array($ecode, [2601, 2627])) {
            // pass
        } else {
            throw $e;
        }
    }

    /**
     * Create a json string from the given array.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @return string
     *
     * @throws \Illuminate\Queue\InvalidPayloadException
     */
    protected function jsonize($data)
    {
        $json = json_encode($data);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidPayloadException(
                'Unable to JSON encode payload. Error code: '.json_last_error()
            );
        }

        return $json;
    }
}
