<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."invoice";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        $tableNameVendor = Config::get('constants.TBL_PREFIX')."vendor";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameVendor,$tableNameUser){
                $table->increments('id');
                $table->unsignedInteger('user_id')->default(0)->comment('tbl_fm_user autoincrement id');
                $table->string('invoice_number',50)->nullable();
                $table->string('invoice_for',30)->nullable();
                $table->string('invoice_type',30)->nullable();
                $table->unsignedInteger('vendor_id')->default(0)->comment('tbl_fm_vendor autoincrement id');
                $table->date('invoice_date')->default('0000-00-00');
                $table->string('invoice_file',30)->nullable();
                $table->double('gst',10,2)->nullable();
                $table->double('total_amount',10,2)->nullable();
                $table->double('paid_amount',10,2)->nullable();
                $table->double('due_amount',10,2)->nullable();
                $table->double('order_number',10,2)->nullable();
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->unsignedSmallInteger('change_status')->default(0);
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
        $tableName = Config::get('constants.TBL_PREFIX')."invoice";
        Schema::dropIfExists($tableName);
    }
}
