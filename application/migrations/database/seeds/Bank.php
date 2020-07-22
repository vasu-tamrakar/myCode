<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;


class Bank extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tableName = Config::get('constants.TBL_PREFIX')."bank";
        $json = File::get(Config::get('constants.JSON_FILE_PATH').$tableName.".json");
        $columns = (DB::getSchemaBuilder()->getColumnListing($tableName));
        $fIlterFun= Config::get('constants.JSON_FILTER_KEY_FUN');
        
        $queryData = (array) json_decode($json, true);
        foreach ($queryData as $objData) {
            $obj=$fIlterFun($objData,$columns);
            DB::table($tableName)->updateOrInsert(['id' => $obj['id']],
            $obj);
        }
    }
}
