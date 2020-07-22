<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableVendorMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_mapping";
        $tableNameVendor = Config::get('constants.TBL_PREFIX')."vendor";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameVendor,$tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('vendor_id')->default(0)->comment('tbl_fm_vendor autoincrement id');
                $table->unsignedInteger('user_id')->default(0)->comment('tbl_fm_user autoincrement id');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->foreign('vendor_id')->references('id')->on($tableNameVendor)->onDelete('cascade')->onUpdate('no action');
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
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_mapping";
        Schema::dropIfExists($tableName);
    }
}
