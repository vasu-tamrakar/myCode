<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableCategoryMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."category_mapping";
        $tableNameCategory = Config::get('constants.TBL_PREFIX')."category";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table) use($tableNameCategory,$tableName,$tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('category_id')->default(0)->comment('tbl_fm_category autoincrement id');
                $table->unsignedInteger('sub_category_id')->default(0)->comment('tbl_fm_category parent id');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->foreign('category_id')->references('id')->on($tableNameCategory)->onDelete('cascade')->onUpdate('no action');
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
        /* $tableName = Config::get('constants.TBL_PREFIX')."category_mapping";
        Schema::dropIfExists($tableName); */
    }
}
