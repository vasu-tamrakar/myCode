<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableInvoiceLineItemAddColumnAiSent extends Migration
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
                if (!Schema::hasColumn($tableName, 'ai_sent')) {
                    $table->unsignedTinyInteger('ai_sent')->default(0)->comment('AI sent=1 not sent=0')->before('flag');
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
              
                if (Schema::hasColumn($tableName, 'ai_sent')) {
                    $table->dropColumn('ai_sent');
                }
                
            });
        }
    }
}
