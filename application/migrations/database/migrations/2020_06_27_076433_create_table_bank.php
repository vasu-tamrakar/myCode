<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableBank extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."bank";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->string('bank_name',100);
                $table->unsignedSmallInteger('bank_type')->default(1)->comment('1-bank statment and 2 for credit card');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
            });
            if (Schema::hasTable($tableName)) {
                $seederObj = new Bank();
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
         $tableName = Config::get('constants.TBL_PREFIX')."bank";
        Schema::dropIfExists($tableName);
    }
}
