<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableForecastingStatementLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."forecasting_statement_line_item";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
       
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameUser){
                $table->increments('id');
                $table->unsignedSmallInteger('transaction_type')->comment('1 - Credit/ 2 - Debit');
                $table->date('transaction_date')->default('0000-00-00')->comment('appoximate expenses/credit date');
                $table->unsignedInteger('category_id')->nullable()->comment('which type of expenses/credit category');
                $table->double('amount',10,2)->default(0.00)->comment('total amount expenses/credit');
                $table->unsignedInteger('user_id')->nullable()->comment('tbl_fm_user auto incremnet id');
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
        $tableName = Config::get('constants.TBL_PREFIX')."forecasting_statement_line_item";
        Schema::dropIfExists($tableName);
    }
}
