<?php

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
        if (!Schema::hasColumn('jobs', 'unique_id')) {
            Schema::table('jobs', function($table) {
                $table->string('unique_id')->nullable();
                $table->unique(['queue', 'unique_id'], 'jobs_queue_unique_id_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('jobs', 'unique_id')) {
            Schema::table('jobs', function (Blueprint $table) {
                $table->dropUnique('jobs_queue_unique_id_unique');
                $table->dropColumn('unique_id');
            });
        }
    }
}
