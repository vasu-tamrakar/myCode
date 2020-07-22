<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableVendorChangeForeignkeyCategoryIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        $tableNameCategory = Config::get('constants.TBL_PREFIX')."category";
        $foreignKeyFun= Config::get('constants.LIST_TABLE_FOREIGNKEY');
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName,$foreignKeyFun,$tableNameCategory) {
                $list = $foreignKeyFun($tableName);
                
                $foreignKeyNameCategoryId = $tableName."_category_id_foreign";
                if (Schema::hasColumn($tableName, 'category_id') && in_array($foreignKeyNameCategoryId, $list)) {
                    $table->dropForeign($foreignKeyNameCategoryId);
                }
                if (Schema::hasColumn($tableName, 'category_id')) {
                    $table->unsignedInteger('category_id')->nullable()->comment('tbl_fm_category auto increment id')->change();
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
