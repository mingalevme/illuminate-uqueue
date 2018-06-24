<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

trait PackageTest
{
    public function testSimpleDatabaseJob()
    {
        Queue::setDefaultDriver('database');

        $id1 = Queue::push(new SimpleJob(['foo' => 'bar']));
        $id2 = Queue::push(new SimpleJob(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertNotSame((string) $id1, (string) $id2);

        $this->assertCount(2, DB::select('SELECT * FROM jobs'));

        SimpleJob::$test = null;

        /** @var DatabaseJob $job */
        $job = Queue::pop();

        $job->fire();

        $this->assertTrue(SimpleJob::$test);
    }

    public function testUniqueableDatabaseJob()
    {
        Queue::setDefaultDriver('database');

        $id1 = Queue::push(new UniqueableJob(['foo' => 'bar']));
        $id2 = Queue::push(new UniqueableJob(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertSame((string) $id1, (string) $id2);

        $this->assertCount(1, DB::select('SELECT * FROM jobs'));

        $id3 = Queue::push(new UniqueableJob(['foo2' => 'bar2']));

        $this->assertNotSame($id1, $id3);

        $this->assertCount(2, DB::select('SELECT * FROM jobs'));

        UniqueableJob::$test = null;

        /** @var DatabaseJob $job */
        $job = Queue::pop();

        $job->fire();

        $this->assertTrue(UniqueableJob::$test);
    }

    public function testSimpleRedisJob()
    {
        Queue::setDefaultDriver('redis');

        Redis::command('DEL', ['queues:default']);

        $id1 = Queue::push(new SimpleJob(['foo' => 'bar']));
        $id2 = Queue::push(new SimpleJob(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertNotSame($id1, $id2);

        $this->assertCount(2, Redis::command('ZRANGE', ['queues:default', 0, -1]));

        SimpleJob::$test = null;

        /** @var RedisJob $job */
        $job = Queue::pop();

        $job->fire();

        $this->assertTrue(SimpleJob::$test);
    }

    public function testUniqueableRedisJob()
    {
        Queue::setDefaultDriver('redis');

        Redis::command('DEL', ['queues:default']);

        $id1 = Queue::push(new UniqueableJob(['foo' => 'bar']));
        $id2 = Queue::push(new UniqueableJob(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertSame($id1, $id2);

        $this->assertCount(1, Redis::command('ZRANGE', ['queues:default', 0, -1]));

        $id3 = Queue::push(new UniqueableJob(['foo2' => 'bar2']));

        $this->assertNotSame($id1, $id3);

        $this->assertCount(2, Redis::command('ZRANGE', ['queues:default', 0, -1]));

        UniqueableJob::$test = null;

        /** @var RedisJob $job */
        $job = Queue::pop();

        $job->fire();

        $this->assertTrue(UniqueableJob::$test);
    }
}
