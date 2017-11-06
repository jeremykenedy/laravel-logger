<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Database Settings
    |--------------------------------------------------------------------------
    */

    'loggerDatabaseConnection'  => env('LARAVEL_LOGGER_DATABASE_CONNECTION', 'mysql'),
    'loggerDatabaseTable'       => env('LARAVEL_LOGGER_DATABASE_TABLE', 'laravel_logger_activity'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Laravel Logger Middlware
    |--------------------------------------------------------------------------
    */

    'loggerMiddlewareEnabled' => env('LARAVEL_LOGGER_MIDDLEWARE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Authentication Listeners Enable/Disable
    |--------------------------------------------------------------------------
    */
    'logAllAuthEvents'      => false,   // May cause a lot of duplication.
    'logAuthAttempts'       => false,   // Successful and Failed -  May cause a lot of duplication.
    'logFailedAuthAttempts' => true,    // Failed Logins
    'logLockOut'            => true,    // Account Lockout
    'logPasswordReset'      => true,    // Password Resets
    'logSuccessfulLogin'    => true,    // Successful Login
    'logSuccessfulLogout'   => true,    // Successful Logout

    /*
    |--------------------------------------------------------------------------
    | Laravel Default User Model
    |--------------------------------------------------------------------------
    */

    'defaultUserModel' => env('LARAVEL_LOGGER_USER_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Pagination Settings
    |--------------------------------------------------------------------------
    */
    'loggerPaginationEnabled' => env('LARAVEL_LOGGER_PAGINATION_ENABLED', true),
    'loggerPaginationPerPage' => env('LARAVEL_LOGGER_PAGINATION_PER_PAGE', 25),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Databales Settings - Not recommended with pagination.
    |--------------------------------------------------------------------------
    */

    'loggerDatatables'              => env('LARAVEL_LOGGER_DATATABLES_ENABLED', false),
    'loggerDatatablesCSScdn'        => 'https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css',
    'loggerDatatablesJScdn'         => 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js',
    'loggerDatatablesJSVendorCdn'   => 'https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js',

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Dashboard Settings
    |--------------------------------------------------------------------------
    */

    'enableSubMenu'     => env('LARAVEL_LOGGER_DASHBOARD_MENU_ENABLED', false),
    'enableDrillDown'   => env('LARAVEL_LOGGER_DASHBOARD_DRILLABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Failed to Log Settings
    |--------------------------------------------------------------------------
    */

    'logDBActivityLogFailuresToFile'     => env('LARAVEL_LOGGER_LOG_RECORD_FAILURES_TO_FILE', true),

];
