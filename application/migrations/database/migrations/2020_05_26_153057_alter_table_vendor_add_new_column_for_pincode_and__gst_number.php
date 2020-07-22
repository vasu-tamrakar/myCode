<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

class AlterTableVendorAddNewColumnForPincodeAndGstNumber extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."vendor";
        

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use($tableName) {
                if (!Schema::hasColumn($tableName, 'gst_number')) {
                    $table->string('gst_number',20)->comment('local vendor gst_number for check unique for local vendor')->nullable();
                }
                if (!Schema::hasColumn($tableName, 'pincode')) {
                    $table->string('pincode',6)->comment('local vendor pincode for check unique for local vendor')->nullable();
                }
                if (Schema::hasColumn($tableName, 'status')) {
                    $table->unsignedSmallInteger('status')->default(1)->comment('(1-pending, 2-approve, 3-disapprove)')->change();
                }
                if (!Schema::hasColumn($tableName, 'vendor_type')) {
                    $table->unsignedSmallInteger('vendor_type')->default(0)->comment('0-N/A, 1-global, 2-local');
                }
                if (!Schema::hasColumn($tableName, 'source_type')) {
                    $table->unsignedSmallInteger('source_type')->default(1)->comment('(1-python, 2-user, 3-admin)');
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
                if (Schema::hasColumn($tableName, 'gst_number')) {
                    $table->dropColumn('gst_number',20);
                }
                if (Schema::hasColumn($tableName, 'pincode')) {
                    $table->dropColumn('pincode',20);
                }
                if (Schema::hasColumn($tableName, 'status')) {
                    $table->unsignedSmallInteger('status')->default(1)->comment('1-active and 2 for deactive')->change();
                }
                if (Schema::hasColumn($tableName, 'vendor_type')) {
                    $table->dropColumn('vendor_type');
                }
                if (Schema::hasColumn($tableName, 'source_type')) {
                    $table->dropColumn('source_type');
                }
                
            });
        }
    }
}
