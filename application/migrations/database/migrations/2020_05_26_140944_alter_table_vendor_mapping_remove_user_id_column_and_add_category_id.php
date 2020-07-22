<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableVendorMappingRemoveUserIdColumnAndAddCategoryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_mapping";
        $tableNameUser = Config::get('constants.TBL_PREFIX')."user";
        $tableNameCategory = Config::get('constants.TBL_PREFIX')."category";
        $foreignKeyFun= Config::get('constants.LIST_TABLE_FOREIGNKEY');
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName,$foreignKeyFun,$tableNameUser,$tableNameCategory) {
                $list = $foreignKeyFun($tableName);
                
                $foreignKeyNameUserId = $tableName."_user_id_foreign";
                $foreignKeyNameCategoryId = $tableName."_category_id_foreign";
                if (Schema::hasColumn($tableName, 'user_id') && in_array($foreignKeyNameUserId, $list)) {
                    $table->dropForeign($foreignKeyNameUserId);
                }
                if (Schema::hasColumn($tableName, 'user_id')) {
                    $table->dropColumn('user_id');
                }
                if (!Schema::hasColumn($tableName, 'category_id')) {
                    $table->unsignedInteger('category_id')->comment('tbl_fm_category autoincrement id')->after('vendor_id');
                }
                if (Schema::hasColumn($tableName, 'category_id') && !in_array($foreignKeyNameCategoryId, $list)) {
                    $table->foreign('category_id')->references('id')->on($tableNameCategory)->onDelete('cascade')->onUpdate('no action');
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
