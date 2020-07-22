<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableStatmentLineItemRemoveMappedIdForeignkey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        $tableName = Config::get('constants.TBL_PREFIX')."statement_line_item";
        $foreignKeyFun= Config::get('constants.LIST_TABLE_FOREIGNKEY');
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName,$foreignKeyFun) {
                $list = $foreignKeyFun($tableName);
                $foreignKeyNameMappedId = $tableName."_mapped_id_foreign";
                if (Schema::hasColumn($tableName, 'mapped_id') && in_array($foreignKeyNameMappedId, $list)) {
                    $table->dropForeign($foreignKeyNameMappedId);
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
