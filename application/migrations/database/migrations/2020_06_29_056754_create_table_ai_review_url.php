<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableAiReviewUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."ai_review_url";
        if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table){
                $table->increments('id');
                $table->dateTime('from_date')->default('0000-00-00 00:00:00');
                $table->dateTime('to_date')->default('0000-00-00 00:00:00');
                $table->string('complete_url',255)->nullable();
                $table->unsignedSmallInteger('type')->default(1)->comment('1-Invoice, 2-Statement');
                $table->unsignedSmallInteger('source_type')->default(1)->comment('1-cron and 2 for admin');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
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
         $tableName = Config::get('constants.TBL_PREFIX')."ai_review_url";
        Schema::dropIfExists($tableName);
    }
}
