<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterAddOriginalIdStatementLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
 
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        Schema::table($tableName, function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fm_statement_line_item', 'original_id')) {
                $table->unsignedTinyInteger('original_id')->nullable()->comment('autoincrement id of statment line item');  
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        Schema::table($tableName, function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fm_statement_line_item', 'original_id')) {
                $table->dropColumn('original_id');  
            }
        });
    }
}
