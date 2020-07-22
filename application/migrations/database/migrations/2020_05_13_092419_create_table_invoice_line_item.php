<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class CreateTableInvoiceLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."invoice_line_item";
        $tableNameInvoice = Config::get('constants.TBL_PREFIX')."invoice";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        $tableNameVendor = Config::get('constants.TBL_PREFIX')."vendor";
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use($tableNameInvoice){
                $table->increments('id');
                $table->unsignedInteger('invoice_id')->default(0)->comment('tbl_fm_invoice autoincrement id');
                $table->string('item_description',255)->nullable();
                $table->date('invoice_date')->default('0000-00-00');
                $table->unsignedInteger('mapping_id')->nullable();
                $table->unsignedInteger('qty');
                $table->double('unit_price',10,2);
                $table->double('gst',10,2);
                $table->double('amount',10,2);
                $table->unsignedSmallInteger('status')->default(1)->comment('1-active, 2-deactive');
                $table->unsignedSmallInteger('archive')->default(0)->comment('1-archive,0-not');
                $table->dateTime('created')->default('0000-00-00 00:00:00');
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->foreign('invoice_id')->references('id')->on($tableNameInvoice)->onDelete('cascade')->onUpdate('no action');
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
        $tableName = Config::get('constants.TBL_PREFIX')."invoice_line_item";
        Schema::dropIfExists($tableName);
    }
}
