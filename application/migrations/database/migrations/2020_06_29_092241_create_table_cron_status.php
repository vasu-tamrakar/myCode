<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableCronStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        $tableName = Config::get('constants.TBL_PREFIX')."cron_status";
       
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->timestamp('last_date_time')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('cron start time');
                $table->string('method_name',255)->nullable()->comment('method for which type of cron set');
                $table->smallInteger('status')->unsigned()->default('1')->comment('1-active and 0- inactive');
                $table->text('response')->nullable()->comment('response_data');
                $table->dateTime('created_date')->default('0000-00-00 00:00:00');
                
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
        $tableName = Config::get('constants.TBL_PREFIX')."cron_status";
        Schema::dropIfExists($tableName);
    }
}
