<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* $tableName = Config::get('constants.TBL_PREFIX')."email";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0)->comment('table depend on user_type column value tbl_fm_user/vendors')->index();
                $table->unsignedSmallInteger('user_type')->default(0)->comment('1-user and 2 vendor');
                $table->string('email',100)->nullable();
                $table->unsignedSmallInteger('primary_email')->comment('1- Primary, 2- Secondary');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 deactive');
                $table->dateTime('created_date')->default('0000-00-00 00:00:00');
                $table->timestamp('updated_date')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
            });
        } */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /* $tableName = Config::get('constants.TBL_PREFIX')."email";
        Schema::dropIfExists($tableName); */
    }
}
