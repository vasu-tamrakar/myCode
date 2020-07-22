<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableStatementLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        $tableNameStatment = Config::get('constants.TBL_PREFIX')."statement";
        $tableNameVendorMapping = Config::get('constants.TBL_PREFIX')."vendor_mapping";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameStatment, $tableNameVendorMapping){
                $table->increments('id');
                $table->unsignedInteger('statement_id')->comment('tbl_fm_statement autoincrement id');
                $table->date('transaction_date')->default('0000-00-00');
                $table->string('description',255)->nullable();
                $table->unsignedInteger('mapped_id')->nullable();
                $table->double('amount',10,2)->nullable();
                $table->double('credit_amt',10,2)->nullable();
                $table->double('debit_amt',10,2)->nullable();
                $table->unsignedSmallInteger('transaction_type')->comment('1 - Credit/ 2 - Debit');
                $table->string('cheque_number',50)->nullable();
                $table->double('main_balance',10,2)->nullable();
                $table->unsignedSmallInteger('read_status')->default(1);
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('statement_id')->references('id')->on($tableNameStatment)->onDelete('cascade')->onUpdate('no action');
                $table->foreign('mapped_id')->references('id')->on($tableNameVendorMapping)->onDelete('cascade')->onUpdate('no action');
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
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        Schema::dropIfExists($tableName);
    }
}
