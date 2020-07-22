<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableVendorAddColumnNameDropCategoryPersonIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_fm_vendor', function (Blueprint $table) {
            //
        });
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        $tableNamePerson = Config::get('constants.TBL_PREFIX')."person";
        $foreignKeyFun= Config::get('constants.LIST_TABLE_FOREIGNKEY');
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName,$foreignKeyFun,$tableNamePerson) {
                $list = $foreignKeyFun($tableName);
                
                $foreignKeyNamePresonId = $tableName."_person_id_foreign";
                if (Schema::hasColumn($tableName, 'person_id') && in_array($foreignKeyNamePresonId, $list)) {
                    $table->dropForeign($foreignKeyNamePresonId);
                }
                
                $foreignKeyNameCategoryId = $tableName."_category_id_foreign";
                if (Schema::hasColumn($tableName, 'category_id') && in_array($foreignKeyNameCategoryId, $list)) {
                    $table->dropForeign($foreignKeyNameCategoryId);
                }

                if (Schema::hasColumn($tableName, 'person_id')) {
                    $table->dropColumn('person_id');
                }
                if (Schema::hasColumn($tableName, 'category_id')) {
                    $table->dropColumn('category_id');
                }
                if (!Schema::hasColumn($tableName, 'name')) {
                    $table->string('name', 255)->after('id');
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
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
              
                if (Schema::hasColumn($tableName, 'name')) {
                    $table->dropColumn('name');
                }
                
            });
        }
    }
}
