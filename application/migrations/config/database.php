<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Like any Laravel application, you can configure and use different
    | database connections. Although you are most likely to only use
    | a single database connection for Nomad, the choice is there.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('FINANCE_MANAGER_DB_NAME', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('FINANCE_MANAGER_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('FINANCE_MANAGER_DB_NAME', 'finance_manager'),
            'username' => trim(env('FINANCE_MANAGER_DB_USERNAME', 'root')),
            'password' => trim(env('FINANCE_MANAGER_DB_PASSWORD', '')),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('FINANCE_MANAGER_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('HCM_WEBSITE_DB_NAME', 'finance_manager'),
            'username' => env('FINANCE_MANAGER_DB_USERNAME', 'root'),
            'password' => env('FINANCE_MANAGER_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('FINANCE_MANAGER_DB_HOST_NAME', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('HCM_WEBSITE_DB_NAME', 'finance_manager'),
            'username' => env('FINANCE_MANAGER_DB_USERNAME', 'root'),
            'password' => env('FINANCE_MANAGER_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

];
