<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
 
class AlterTableStatementChangeIssueAndDueDateFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."statement";
        

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                if (Schema::hasColumn($tableName, 'issue_date') && Schema::hasColumn($tableName, 'due_date')) {
                    $table->date('issue_date')->default('0000-00-00')->nullable()->change();
                    $table->date('due_date')->default('0000-00-00')->nullable()->change();
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
