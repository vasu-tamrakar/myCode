<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableLogsActivityTypeAddNewColumnUpdated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."logs_activity_type";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
              
                if (!Schema::hasColumn($tableName, 'source_type')) {
                    $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        $tableName = Config::get('constants.TBL_PREFIX')."logs_activity_type";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
              
                if (Schema::hasColumn($tableName, 'updated')) {
                    $table->dropColumn('updated');
                }
                
            });
        }
    }
}
