<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableUserLastFetchTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."user_last_fetch_time";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('user_id')->comment('tbl_fm_user id');
                $table->dateTime('last_fetch')->default('0000-00-00 00:00:00');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->foreign('user_id')->references('id')->on($tableNameUser)->onDelete('cascade')->onUpdate('no action');
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
        $tableName = Config::get('constants.TBL_PREFIX')."user_last_fetch_time";
        Schema::dropIfExists( $tableName);
    }
}
