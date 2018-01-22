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
    | Laravel Logger Roles Settings - (laravel roles not required if false)
    |--------------------------------------------------------------------------
    */

    'rolesEnabled' => env('LARAVEL_LOGGER_ROLES_ENABLED', false),
    'rolesMiddlware' => env('LARAVEL_LOGGER_ROLES_MIDDLWARE', 'role:admin'),

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

    'defaultUserModel' => env('LARAVEL_LOGGER_USER_MODEL', 'App\User'),

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

    'enableSubMenu'     => env('LARAVEL_LOGGER_DASHBOARD_MENU_ENABLED', true),
    'enableDrillDown'   => env('LARAVEL_LOGGER_DASHBOARD_DRILLABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Failed to Log Settings
    |--------------------------------------------------------------------------
    */

    'logDBActivityLogFailuresToFile' => env('LARAVEL_LOGGER_LOG_RECORD_FAILURES_TO_FILE', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Flash Messages
    |--------------------------------------------------------------------------
    */

    'enablePackageFlashMessageBlade' => env('LARAVEL_LOGGER_FLASH_MESSAGE_BLADE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Dependencies - allows for easier builds into other projects
    |--------------------------------------------------------------------------
    */

    // jQuery
    'enablejQueryCDN'           => env('LARAVEL_LOGGER_JQUERY_CDN_ENABLED', true),
    'JQueryCDN'                 => env('LARAVEL_LOGGER_JQUERY_CDN_URL', 'https://code.jquery.com/jquery-2.2.4.min.js'),

    // Blade Extension Placement
    'enableBladeCssPlacement'   => env('LARAVEL_LOGGER_BLADE_CSS_PLACEMENT_ENABLED', false),
    'enableBladeJsPlacement'    => env('LARAVEL_LOGGER_BLADE_JS_PLACEMENT_ENABLED', false),

    // Bootstrap
    'enableBootstrapCssCDN'     => env('LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_ENABLED', true),
    'bootstrapCssCDN'           => env('LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_URL', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'),
    'enableBootstrapJsCDN'      => env('LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_ENABLED', true),
    'bootstrapJsCDN'            => env('LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_URL', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'),

    // Font Awesome
    'enableFontAwesomeCDN'      => env('LARAVEL_LOGGER_FONT_AWESOME_CDN_ENABLED', true),
    'fontAwesomeCDN'            => env('LARAVEL_LOGGER_FONT_AWESOME_CDN_URL', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'),

];
