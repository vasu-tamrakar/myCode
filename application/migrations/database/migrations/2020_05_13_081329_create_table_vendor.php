<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        $tableNamePerson = Config::get('constants.TBL_PREFIX')."person";
        $tableNameCategory = Config::get('constants.TBL_PREFIX')."category";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNamePerson,$tableNameCategory){
                $table->increments('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_fm_person auto increment id');
                $table->unsignedInteger('category_id')->comment('tbl_fm_category auto increment id');
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive');
                $table->unsignedInteger('created_by')->default(0);
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->foreign('person_id')->references('id')->on($tableNamePerson)->onDelete('CASCADE'); // destroy related row if a row in tbl_person.id is also destroyed
                $table->foreign('category_id')->references('id')->on($tableNameCategory)->onDelete('no action')->onUpdate('no action'); // destroy related row if a row in tbl_person.id is also destroyed
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
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        Schema::dropIfExists($tableName);
    }
}
