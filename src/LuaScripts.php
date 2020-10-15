<?php

namespace Mingalevme\Illuminate\UQueue;

class LuaScripts extends \Illuminate\Queue\LuaScripts
{
    /**
     * Get the Lua script for computing the size of queue.
     *
     * KEYS[1] - The name of the primary queue
     * KEYS[2] - The name of the "delayed" queue
     * KEYS[3] - The name of the "reserved" queue
     *
     * @return string
     */
    public static function size()
    {
        return <<<'LUA'
return redis.call('zcard', KEYS[1]) + redis.call('zcard', KEYS[2]) + redis.call('zcard', KEYS[3])
LUA;
    }

    /**
     * Get the Lua script for pushing jobs onto the queue.
     *
     * KEYS[1] - The queue to push the job onto, for example: queues:foo
     * KEYS[2] - The notification list fot the queue we are pushing jobs onto, for example: queues:foo:notify
     * ARGV[1] - Score (The current UNIX timestamp)
     * ARGV[2] - The job payload
     *
     * @return string
     */
    public static function push()
    {
        return <<<'LUA'
-- Push the job onto the queue...
redis.call('zadd', KEYS[1], 'NX', ARGV[1], ARGV[2])
-- Push a notification onto the "notify" queue...
redis.call('rpush', KEYS[2], 1)
LUA;
    }
    
    /**
     * Get the Lua script to migrate expired jobs back onto the queue.
     *
     * KEYS[1] - The queue we are removing jobs from, for example: queues:foo:reserved
     * KEYS[2] - The queue we are moving jobs to, for example: queues:foo
     * KEYS[3] - The notification list for the queue we are moving jobs to, for example queues:foo:notify
     * ARGV[1] - Score (The current UNIX timestamp)
     *
     * @return string
     */
    public static function migrateExpiredJobs()
    {
        return <<<'LUA'
-- Get all of the jobs with an expired "score"...
local val = redis.call('zrangebyscore', KEYS[1], '-inf', ARGV[1])

-- If we have values in the array, we will remove them from the first queue
-- and add them onto the destination queue in chunks of 100, which moves
-- all of the appropriate jobs onto the destination queue very safely.
if(next(val) ~= nil) then
    -- Remove all selected jobs from source queue
    redis.call('zremrangebyrank', KEYS[1], 0, #val - 1)
    
    local chunk = {}
    local chunk_size = 100
    
    for i = 1, #val, chunk_size do
        chunk = {}    
        for j = i, math.min(i+chunk_size-1, #val) do
            table.insert(chunk, tonumber(ARGV[1]) + j/1000)
            table.insert(chunk, val[j])
            -- Push a notification for every job that was migrated...
            redis.call('rpush', KEYS[3], 1)
        end
        -- NX: Don't update already existing elements. Always add new elements.
        redis.call('zadd', KEYS[2], 'NX', unpack(chunk))
    end
end

return val
LUA;
    }
    
    /**
     * Get the Lua script for popping the next job off of the queue.
     *
     * KEYS[1] - The queue to pop jobs from, for example: queues:foo
     * KEYS[2] - The queue to place reserved jobs on, for example: queues:foo:reserved
     * KEYS[3] - The notify queue
     * ARGV[1] - The time at which the reserved job will expire
     *
     * @return string
     */
    public static function pop()
    {
        return <<<'LUA'
-- Pop the first job off of the queue...
local jobs = redis.call('zrange', KEYS[1], 0, 0)

while #jobs > 0 do
    -- Check that no one has reserved the job
    if (redis.call('zrem', KEYS[1], jobs[1]) > 0) then
        -- Increment the attempt count and place job on the reserved queue...
        local reserved = cjson.decode(jobs[1])
        reserved['attempts'] = reserved['attempts'] + 1
        reserved = cjson.encode(reserved)
        redis.call('zadd', KEYS[2], ARGV[1], reserved)
        redis.call('lpop', KEYS[3])
        return {jobs[1], reserved}
    end
    jobs = redis.call('zrange', KEYS[1], 0, 0)
end

return {false, false}
LUA;
    }
}
