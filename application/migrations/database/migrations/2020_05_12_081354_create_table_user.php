<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."user";
        $tableNamePerson = Config::get('constants.TBL_PREFIX')."person";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table) use($tableNamePerson){
                $table->increments('id');
                $table->unsignedSmallInteger('token_status')->default(0)->comment('reset password 1 for active reset, 0 for notactive');
                $table->text('token')->nullable();
                $table->unsignedBigInteger('person_id')->comment('tbl_fm_person auto increment id');
                $table->string('username',20)->nullable();
                $table->string('email',100)->nullable()->index();
                $table->text('password')->nullable();
                $table->string('pin',64)->nullable();
                $table->string('profile_image',255)->nullable();
                $table->string('prefer_contact',50)->nullable();
                $table->string('otp',255)->nullable();
                $table->dateTime('otp_expire_time')->default('0000-00-00 00:00:00');
                $table->unsignedSmallInteger('loginattempt')->default('0');
                $table->unsignedSmallInteger('status')->default('1')->comment('1-active and 2 for deactive')->index();
                $table->unsignedSmallInteger('email_verify')->default('1')->comment('0 for active / 1 for not active (for after registration verify email only)');
                $table->unsignedInteger('user_timezone')->default('0')->comment('0 utc and more  then o then tbl_fm_country_timezone');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->foreign('person_id')->references('id')->on($tableNamePerson)->onDelete('CASCADE'); // destroy related row if a row in tbl_person.id is also destroyed
                
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
        $tableName = Config::get('constants.TBL_PREFIX')."user";
        Schema::dropIfExists($tableName);
    }
}
