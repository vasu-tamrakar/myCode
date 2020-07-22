<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableVendorUserMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_user_mapping";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        $tableNameVendor = Config::get('constants.TBL_PREFIX')."vendor";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser,$tableNameVendor){
                $table->increments('id');
                $table->unsignedInteger('user_id')->comment('tbl_fm_user autoincrement id');
                $table->unsignedInteger('vendor_id')->comment('tbl_fm_vendor autoincrement id');
                $table->dateTime('created')->default('0000-00-00 00:00:00')->comment('utc date time format');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->foreign('user_id')->references('id')->on($tableNameUser)->onDelete('cascade')->onUpdate('no action');
                $table->foreign('vendor_id')->references('id')->on($tableNameVendor)->onDelete('cascade')->onUpdate('no action');
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
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_user_mapping";
        Schema::dropIfExists($tableName);
    }
}
