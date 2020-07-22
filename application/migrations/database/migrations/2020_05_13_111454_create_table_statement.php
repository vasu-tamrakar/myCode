<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."statement";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0)->comment('tbl_fm_user autoincrement id');
                $table->string('statement_number',50)->nullable();
                $table->dateTime('issue_date')->default('0000-00-00 00:00:00');
                $table->dateTime('due_date')->default('0000-00-00 00:00:00');
                $table->string('statement_notes',255)->nullable();
                $table->string('statement_file_path',255)->nullable();
                $table->string('statement_file_name',255)->nullable();
                $table->string('statement_type',30)->nullable();
                $table->string('statement_for',50)->nullable();
                $table->unsignedSmallInteger('booked_by')->default(0);
                $table->unsignedSmallInteger('booker_mail')->default(0);
                $table->double('total',10,2)->default(0);
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->unsignedSmallInteger('change_status')->default(0)->comment('1-fetch, 2-read');
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
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
        $tableName = Config::get('constants.TBL_PREFIX')."statement";
        Schema::dropIfExists($tableName);
    }
}
