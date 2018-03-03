<?php

namespace Mingalevme\Tests\Illuminate\UQueue;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mingalevme\Illuminate\UQueue\Jobs\Uniqueable;

class Job implements Uniqueable, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function uniqueable()
    {
        return md5(json_encode($this->data));
    }

    public function handle()
    {
        // pass
    }
}
