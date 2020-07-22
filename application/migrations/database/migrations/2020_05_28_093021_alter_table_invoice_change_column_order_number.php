<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableInvoiceChangeColumnOrderNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."invoice";
        

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                if (Schema::hasColumn($tableName, 'order_number')) {
                    $table->string('order_number',50)->nullable()->change();
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
        
    }
}
