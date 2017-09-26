# illuminate-uqueue
Provides support for uniqueable queues for Laravel/Lumen 5.5.

# Supported drivers:
- Database
- Redis (Based on Sorted Sets)

# Installation

1. Register the service provider ```\Mingalevme\Illuminate\UQueue\UQueueServiceProvider::class```.
2. If you plan to use the database as a driver you shoud add the migration (change the table name if necessary):
```php
<?php // /src/migrations/2017_01_01_000000_jobs_add_uniqueable.php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobsAddUniqueable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function($table) {
            $table->text('unique_id')->nullable();
            $table->unique(['queue', 'unique_id'], 'jobs_queue_unique_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropUnique('jobs_queue_unique_id_unique');
            $table->dropColumn('unique_id');
        });
    }
}

```
3. Create a job that implements the interface ```\Mingalevme\Illuminate\UQueue\Jobs\Uniqueable```:
```php
<?php

namespace App\Jobs;

use Mingalevme\Illuminate\UQueue\Jobs\Uniqueable;

class ExampleJob implements Uniqueable
{
    protected $data;
    
    public function __construct(array $data)
    {
        ksort($data);
        $this->data = $data;
    }
    
    public function uniqueable()
    {
        return md5(json_encode($data));
    }
    
    public function handle()
    {
        // ...
    }
}
```
