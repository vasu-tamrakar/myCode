<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableBankPassword extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."bank_password";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0)->comment('tbl_fm_user autoincrement id');
                $table->unsignedBigInteger('bank_id')->comment('tbl_fm_bank auto increment id');
                $table->string('password', 255);
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
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
         $tableName = Config::get('constants.TBL_PREFIX')."bank_password";
        Schema::dropIfExists($tableName);
    }
}
