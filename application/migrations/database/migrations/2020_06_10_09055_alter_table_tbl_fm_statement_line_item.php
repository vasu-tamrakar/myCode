<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableTblFmStatementLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                if (!Schema::hasColumn($tableName, 'flag')){
                    $table->unsignedSmallInteger('flag')->default(0)->comment('(0-No Change, 1-Changed)');
                } 

                if(!Schema::hasColumn($tableName, 'original_map_id')) {
                    $table->unsignedSmallInteger('original_map_id')->default(0)->comment('(0-No Change n-original mapped_id of same row)');
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
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
              
                if (Schema::hasColumn($tableName, 'flag')) {
                    $table->dropColumn('flag');
                }

                if (Schema::hasColumn($tableName, 'original_map_id')) {
                    $table->dropColumn('original_map_id');
                }
                
            });
        }
    }
}
