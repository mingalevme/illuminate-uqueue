<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\RedisJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

trait PackageTest
{
    public function tearDown(): void
    {
        if (Queue::getDefaultDriver() === 'database') {
            $this->migrateDown();
        }
        parent::tearDown();
    }

    protected function migrateDown(): void
    {
        (new \JobsAddUniqueable())->down();
        (new \CreateJobsTable())->down();
    }

    protected function migrateUp(): void
    {
        (new \CreateJobsTable())->up();
        (new \JobsAddUniqueable())->up();
    }

    protected function setUpSqlite(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['queue.default' => 'database']);

        DB::setDefaultConnection('sqlite');

        $this->migrateDown();
        $this->migrateUp();

        Queue::setDefaultDriver('database');
    }

    protected function setUpPgsql(): void
    {
        config(['database.default' => 'pgsql']);
        config(['database.connections.pgsql.database' => 'postgres']);
        config(['database.connections.pgsql.username' => 'postgres']);
        config(['database.connections.pgsql.password' => 'postgres']);
        config(['database.connections.pgsql.port' => '54320']);
        config(['queue.default' => 'database']);

        DB::setDefaultConnection('pgsql');

        $this->migrateDown();
        $this->migrateUp();

        Queue::setDefaultDriver('database');
    }

    protected function setUpMysql(): void
    {
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'mysql']);
        config(['database.connections.mysql.username' => 'mysql']);
        config(['database.connections.mysql.password' => 'mysql']);
        config(['database.connections.mysql.port' => '33060']);
        config(['database.connections.mysql.version' => 8]);
        config(['database.connections.mysql.modes' => [
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'NO_ZERO_IN_DATE',
            'NO_ZERO_DATE',
            'ERROR_FOR_DIVISION_BY_ZERO',
            'NO_ENGINE_SUBSTITUTION',
        ]]);

        config(['queue.default' => 'database']);

        DB::setDefaultConnection('mysql');

        $this->migrateDown();
        $this->migrateUp();

        Queue::setDefaultDriver('database');
    }

    public function testSimpleDatabaseJobSqlite()
    {
        $this->setUpSqlite();

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

    public function testUniqueableDatabaseJobSqlite()
    {
        $this->setUpSqlite();

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

    public function testSimpleDatabaseJobPgsql()
    {
        $this->setUpPgsql();

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

    public function testUniqueableDatabaseJobPgsql()
    {
        $this->setUpPgsql();

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

    public function testSimpleDatabaseJobMysql()
    {
        $this->setUpMysql();

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

    public function testUniqueableDatabaseJobMysql()
    {
        $this->setUpMysql();

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
