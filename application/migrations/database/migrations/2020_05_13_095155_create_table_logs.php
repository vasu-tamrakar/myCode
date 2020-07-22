<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."logs";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('activity_id')->default(0)->comment('primary key of tbl_fm_logs_activity_type')->index();
                $table->text('title')->nullable()->comment('when activity_id 0 then this column value use otherwise not used');
                $table->text('specific_title')->nullable()->comment('for specific or same as activity details or titlevalue');
                $table->text('description')->nullable()->comment('all requested data in json form');
                $table->unsignedBigInteger('created_by')->default(0)->comment('tbl_fm_person id')->index();
                $table->unsignedInteger('created_for')->default(0)->comment('tbl_fm used table autoincrment id');
                $table->unsignedSmallInteger('created_type')->default(0)->comment('0-not known 1-user,2-vendor,3-invoice,4-statement');
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        $tableName = Config::get('constants.TBL_PREFIX')."logs";
        Schema::dropIfExists($tableName);
    }
}
