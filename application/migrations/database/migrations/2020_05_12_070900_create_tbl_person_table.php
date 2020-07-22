<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTblPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Persons table is a generalized table for all 'contact-able' entities. 
        // In FM contact-ables are user, vendor etc...
        $tableNamePerson = Config::get('constants.TBL_PREFIX')."person";
        $tableNamePersonEmail = Config::get('constants.TBL_PREFIX')."person_email";
        $tableNamePersonPhone = Config::get('constants.TBL_PREFIX')."person_phone";
        $tableNamePersonAddress = Config::get('constants.TBL_PREFIX')."person_address";
        // Anyone that has email or phone can be put in this table
        if ( !Schema::hasTable($tableNamePerson)) {
            Schema::create($tableNamePerson, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('firstname', 255);
                $table->string('lastname', 255)->nullable();
                $table->unsignedSmallInteger('type')->default(0)->comment('1-user, 2-vendor ,3-admin user');
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1');
                $table->unsignedSmallInteger('status')->default(1)->comment('deactive=2, active=1');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            });
        }
        
        // Emails
        if ( !  Schema::hasTable($tableNamePersonEmail)) {
            Schema::create($tableNamePersonEmail, function (Blueprint $table) use($tableNamePerson) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_fm_person auto increment id');
                $table->string('email', 100);
                $table->unsignedSmallInteger('archive')->default(0)->comment('no=0, yes=1');
                $table->unsignedTinyInteger('primary_email')->comment('primary=1, secondary=2');

                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));

                $table->foreign('person_id')->references('id')->on($tableNamePerson)->onDelete('CASCADE'); // destroy related row if a row in tbl_fm_person auto increment id is also destroyed
            });
        }
        
        // Phones
        if ( !  Schema::hasTable($tableNamePersonPhone)) {
            Schema::create($tableNamePersonPhone, function (Blueprint $table) use($tableNamePerson)  {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_fm_person auto increment id');
                $table->string('phone', 255);
                $table->unsignedSmallInteger('archive')->comment('no=0, yes=1');
                $table->unsignedTinyInteger('primary_phone')->comment('primary=1, secondary=2');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));

                $table->foreign('person_id')->references('id')->on($tableNamePerson)->onDelete('CASCADE'); // destroy related row if a row in tbl_fm_person auto increment id is also destroyed
            });
        }

        if ( !  Schema::hasTable($tableNamePersonAddress)) {
            Schema::create($tableNamePersonAddress, function (Blueprint $table) use($tableNamePerson)  {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('person_id')->comment('tbl_fm_person auto increment id');
                $table->unsignedInteger('country_id')->nullable()->comment('tbl_fm_country auto increment id')->index();
                $table->string('street', 255)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('postal', 20)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('lat', 50)->nullable();
                $table->string('long', 50)->nullable();
                $table->unsignedSmallInteger('archive')->comment('no=0, yes=1');
                $table->unsignedTinyInteger('primary_address')->comment('primary=1, secondary=2');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('person_id')->references('id')->on($tableNamePerson)->onDelete('CASCADE'); // destroy related row if a row in tbl_fm_person auto increment id is also destroyed
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
        $tableNamePerson = Config::get('constants.TBL_PREFIX')."person";
        $tableNamePersonEmail = Config::get('constants.TBL_PREFIX')."person_email";
        $tableNamePersonPhone = Config::get('constants.TBL_PREFIX')."person_phone";
        $tableNamePersonAddress = Config::get('constants.TBL_PREFIX')."person_address";
        Schema::dropIfExists($tableNamePersonAddress);
        Schema::dropIfExists($tableNamePersonEmail);
        Schema::dropIfExists($tableNamePersonPhone);
        Schema::dropIfExists($tableNamePerson);
    }
}
