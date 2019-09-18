<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobsAddUniqueable extends Migration
{
    const COLUMN_NAME = 'unique_id';
    const INDEX_NAME = 'jobs_queue_unique_id_unique';

    /**
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn(CreateJobsTable::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(CreateJobsTable::TABLE_NAME, function(Blueprint $table) {
                $table->string(self::COLUMN_NAME)->nullable();
                $table->unique(['queue', self::COLUMN_NAME], self::INDEX_NAME);
            });
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn(CreateJobsTable::TABLE_NAME, self::COLUMN_NAME)) {
            Schema::table(CreateJobsTable::TABLE_NAME, function (Blueprint $table) {
                $table->dropColumn(self::COLUMN_NAME);
            });
        }
    }
}
