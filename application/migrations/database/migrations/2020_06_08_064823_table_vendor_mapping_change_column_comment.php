<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class TableVendorMappingChangeColumnComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        $tableName = Config::get('constants.TBL_PREFIX')."vendor_mapping";
        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'status')) {
                $table->unsignedSmallInteger('status')->default(1)->comment('1-pending and 2 for approve 3 for disapprove')->change();;
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
        Schema::table('tbl_fm_vendor_mapping', function (Blueprint $table) {
            //
        });
    }
}
