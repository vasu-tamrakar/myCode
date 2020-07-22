<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."notification";
        
        if ( !Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('alert_title', 255);
                $table->unsignedSmallInteger('alert_type')->comment('1-user, 2-admin');
                $table->unsignedInteger('user_id')->comment('primary key of tbl_fm_users for alert_type=1 and tbl_fm_user_admin for alert_type= 2');
                $table->text('description')->nullable();
                $table->unsignedSmallInteger('is_read')->default(2)->comment('Read=1/Unread=2');
                $table->unsignedSmallInteger('created_by_type')->comment('1=user 2=admin');
                $table->unsignedInteger('created_by')->comment('primary key of tbl_fm_users for created_by_type	=1 and tbl_fm_user_admin for created_by_type=2');
                $table->unsignedSmallInteger('archive')->default(0)->comment('0-Not /1-Delete');
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
        $tableName = Config::get('constants.TBL_PREFIX')."notification";
        Schema::dropIfExists( $tableName);
    }
}
