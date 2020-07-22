<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableCountry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."country";
        $tableNameTimeZone = Config::get('constants.TBL_PREFIX')."country_timezone";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id');
                $table->string('country_name', 150)->comment('country_name name');
                $table->string('key_name', 150)->comment('unique name');
                $table->string('dail_code',20)->comment('country code like india +91');
                $table->string('country_code',4)->comment('country code in iso2');
                $table->unsignedSmallInteger('archive')->comment('no=0, yes=1');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            });
            if (Schema::hasTable($tableName)) {
                $seederObj = new Country();
                $seederObj->run();
            }
        }

        if ( !Schema::hasTable($tableNameTimeZone)) {
            Schema::create($tableNameTimeZone, function (Blueprint $table) use($tableName)  {
                $table->bigIncrements('id');
                $table->unsignedInteger('country_id')->comment('tbl_fm_country auto increment id');
                $table->string('time_zone_name', 255);
                $table->unsignedTinyInteger('primary_timezone')->comment('primary=1, secondary=2');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->comment('no=0, yes=1');

                $table->foreign('country_id')->references('id')->on($tableName)->onDelete('CASCADE'); // destroy related row if a row in tbl_fm_person auto increment id is also destroyed
            });
            if (Schema::hasTable($tableNameTimeZone)) {
                $seederObj = new CountryTimeZone();
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
        $tableName = Config::get('constants.TBL_PREFIX')."country";
        $tableNameTimeZone = Config::get('constants.TBL_PREFIX')."country_timezone";
        Schema::dropIfExists($tableNameTimeZone);
        Schema::dropIfExists($tableName);
    }
}
