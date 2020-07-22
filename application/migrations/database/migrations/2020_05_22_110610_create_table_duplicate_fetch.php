<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableDuplicateFetch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      $tableName = Config::get('constants.TBL_PREFIX')."duplicate_fetch";
      $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
      if (!Schema::hasTable($tableName)) {
         Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
             $table->increments('id');
             $table->unsignedInteger('user_id')->comment('tbl_fm_user id');
             $table->unsignedInteger('last_fetch_id')->comment('tbl_fm_statement_line_item id');
             $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
             $table->timestamp('last_updated_date')->default(DB::raw('CURRENT_TIMESTAMP'));
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
      $tableName = Config::get('constants.TBL_PREFIX')."duplicate_fetch";
      Schema::dropIfExists( $tableName);
    }
}
