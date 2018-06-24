<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SimpleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public static $test;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        static::$test = true;
    }
}
