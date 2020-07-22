<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableInvoiceLineItemAddColumnSubAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."invoice_line_item";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                if (!Schema::hasColumn($tableName, 'sub_amount')) {
                    $table->double('sub_amount',10,2)->nullable()->comment('qty*unit_price')->after('gst');
                }
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
        Schema::table('tbl_fm_invoice_line_item', function (Blueprint $table) {
            //
        });
    }
}
