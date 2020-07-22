<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableAdminUserLoginHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."admin_user_login_history";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."admin_user";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('user_id')->comment('tbl_fm_admin_user autoincrement id');
                $table->string('ip_address',100)->nullable();
                $table->text('token')->nullable();
                $table->text('user_agent')->nullable();
                $table->dateTime('login_time')->nullable();
                $table->dateTime('logout_time')->nullable();
                $table->dateTime('last_access')->nullable();
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
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
        $tableName = Config::get('constants.TBL_PREFIX')."admin_user_login_history";
        Schema::dropIfExists($tableName);
    }
}
