<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableLogsActivityType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."logs_activity_type";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->string('activity_detail',255);
                $table->string('activity_key',255);
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
            });
            if (Schema::hasTable($tableName)) {
                $seederObj = new LogsactivitytypeSeeder();
                $seederObj->run();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."logs_activity_type";
        Schema::dropIfExists($tableName);
    }
}
