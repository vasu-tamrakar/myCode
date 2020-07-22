<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableUnreadablePdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."unreadable_pdf";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0)->comment('tbl_fm_user autoincrement id');
                $table->string('file_name',255)->nullable();
                $table->string('original_file_name',30)->nullable();
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->unsignedSmallInteger('source_type')->default(0)->comment('1-fetch, 2-cron, 3-upload');
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->unsignedSmallInteger('protected')->default(0)->comment('1-protected,0-unprotected');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        $tableName = Config::get('constants.TBL_PREFIX')."unreadable_pdf";
        Schema::dropIfExists($tableName);
    }
}
