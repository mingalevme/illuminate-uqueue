<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

trait PackageTest
{
    public function testDatabase()
    {
        Queue::setDefaultDriver('database');

        $id1 = Queue::push(new Job(['foo' => 'bar']));
        $id2 = Queue::push(new Job(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertSame($id1, $id2);

        $this->assertCount(1, DB::select('SELECT * FROM jobs'));

        $id3 = Queue::push(new Job(['foo2' => 'bar2']));

        $this->assertNotSame($id1, $id3);

        $this->assertCount(2, DB::select('SELECT * FROM jobs'));
    }

    public function testRedis()
    {
        Queue::setDefaultDriver('redis');

        Redis::command('DEL', ['queues:default']);

        $id1 = Queue::push(new Job(['foo' => 'bar']));
        $id2 = Queue::push(new Job(['foo' => 'bar']));

        $this->assertNotNull($id1);
        $this->assertNotNull($id2);
        $this->assertSame($id1, $id2);

        $this->assertCount(1, Redis::command('ZRANGE', ['queues:default', 0, -1]));

        $id3 = Queue::push(new Job(['foo2' => 'bar2']));

        $this->assertNotSame($id1, $id3);

        $this->assertCount(2, Redis::command('ZRANGE', ['queues:default', 0, -1]));
    }
}
