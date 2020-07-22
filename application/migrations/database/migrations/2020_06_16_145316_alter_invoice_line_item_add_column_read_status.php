<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterInvoiceLineItemAddColumnReadStatus extends Migration
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
              
                if (!Schema::hasColumn($tableName, 'read_status')) {
                    $table->unsignedSmallInteger('read_status')->default(1)->comment('(1-unread by python script, 2-read by python script)')->after('mapping_id');
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
        $tableName = Config::get('constants.TBL_PREFIX')."invoice_line_item";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
              
                if (Schema::hasColumn($tableName, 'read_status')) {
                    $table->dropColumn('read_status');
                }
                
            });
        }
    }
}
