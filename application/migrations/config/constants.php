<?php
use Illuminate\Support\Facades\Schema;
return [
	'JSON_FILE_PATH' => base_path()."/database/json_files/",
	'TBL_PREFIX' => "tbl_fm_",
	'JSON_FILTER_KEY_FUN' => function($requestData = [], $fillable = []) {
        $resultData = [];
        if (!empty($requestData) && !empty($fillable)) {
            $resultData = array_intersect_key($requestData, array_flip($fillable));
        }
        return $resultData;
    },
    'LIST_TABLE_FOREIGNKEY'=>function($table) {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
];
?>