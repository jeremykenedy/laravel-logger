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

    'rolesEnabled'   => env('LARAVEL_LOGGER_ROLES_ENABLED', false),
    'rolesMiddlware' => env('LARAVEL_LOGGER_ROLES_MIDDLWARE', 'role:admin'),

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Laravel Logger Middlware
    |--------------------------------------------------------------------------
    */

    'loggerMiddlewareEnabled' => env('LARAVEL_LOGGER_MIDDLEWARE_ENABLED', true),
    'loggerMiddlewareExcept' => array_filter(explode(",", trim(env('LARAVEL_LOGGER_MIDDLEWARE_EXCEPT')))),

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
    | Blade settings
    |--------------------------------------------------------------------------
    */

    // The parent Blade file
    'loggerBladeExtended'       => 'layouts.app',

    // Switch Between bootstrap 3 `panel` and bootstrap 4 `card` classes
    'bootstapVersion'           => '4',

    // Additional Card classes for styling -
    // See: https://getbootstrap.com/docs/4.0/components/card/#background-and-color
    // Example classes: 'text-white bg-primary mb-3'
    'bootstrapCardClasses'          => '',

    // Blade Extension Placement
    'bladePlacement'            => env('LARAVEL_LOGGER_BLADE_PLACEMENT', 'yield'),
    'bladePlacementCss'         => env('LARAVEL_LOGGER_BLADE_PLACEMENT_CSS', 'template_linked_css'),
    'bladePlacementJs'          => env('LARAVEL_LOGGER_BLADE_PLACEMENT_JS', 'footer_scripts'),

    /*
    |--------------------------------------------------------------------------
    | Laravel Logger Dependencies - allows for easier builds into other projects
    |--------------------------------------------------------------------------
    */

    // jQuery
    'enablejQueryCDN'           => env('LARAVEL_LOGGER_JQUERY_CDN_ENABLED', true),
    'JQueryCDN'                 => env('LARAVEL_LOGGER_JQUERY_CDN_URL', 'https://code.jquery.com/jquery-3.2.1.slim.min.js'),

    // Bootstrap
    'enableBootstrapCssCDN'     => env('LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_ENABLED', true),
    'bootstrapCssCDN'           => env('LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_URL', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'),
    'enableBootstrapJsCDN'      => env('LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_ENABLED', true),
    'bootstrapJsCDN'            => env('LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_URL', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'),
    'enablePopperJsCDN'         => env('LARAVEL_LOGGER_POPPER_JS_CDN_ENABLED', true),
    'popperJsCDN'               => env('LARAVEL_LOGGER_POPPER_JS_CDN_URL', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js'),

    // Font Awesome
    'enableFontAwesomeCDN'      => env('LARAVEL_LOGGER_FONT_AWESOME_CDN_ENABLED', true),
    'fontAwesomeCDN'            => env('LARAVEL_LOGGER_FONT_AWESOME_CDN_URL', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'),

];
