<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."category";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('parent_id')->default(0)->comment('0- main category , more then 0 mean sub category of this parent category id');
                $table->string('category_name',100);
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
            });
            if (Schema::hasTable($tableName)) {
                $seederObj = new Category();
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
         $tableName = Config::get('constants.TBL_PREFIX')."category";
        Schema::dropIfExists($tableName);
    }
}
