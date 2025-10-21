<?php

namespace jeremykenedy\LaravelLogger\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up default configuration for testing
        Config::set('LaravelLogger.defaultActivityModel', \jeremykenedy\LaravelLogger\App\Models\Activity::class);
        Config::set('LaravelLogger.defaultUserModel', \App\Models\User::class);
        Config::set('LaravelLogger.defaultUserIDField', 'id');
        Config::set('LaravelLogger.enableDateFiltering', true);
        Config::set('LaravelLogger.enableExport', true);
        Config::set('LaravelLogger.enableSearch', true);
        Config::set('LaravelLogger.loggerPaginationEnabled', false);
        Config::set('LaravelLogger.loggerPaginationPerPage', 25);
        Config::set('LaravelLogger.searchFields', 'description,user,method,route,ip');
        Config::set('LaravelLogger.loggerDatabaseTable', 'laravel_logger_activity');
        Config::set('LaravelLogger.loggerDatabaseConnection', 'mysql');
        Config::set('LaravelLogger.loggerMiddlewareEnabled', true);
        Config::set('LaravelLogger.loggerMiddlewareExcept', []);
        Config::set('LaravelLogger.rolesEnabled', false);
        Config::set('LaravelLogger.rolesMiddlware', 'role:admin');
        Config::set('LaravelLogger.logAllAuthEvents', false);
        Config::set('LaravelLogger.logAuthAttempts', false);
        Config::set('LaravelLogger.logFailedAuthAttempts', true);
        Config::set('LaravelLogger.logLockOut', true);
        Config::set('LaravelLogger.logPasswordReset', true);
        Config::set('LaravelLogger.logSuccessfulLogin', true);
        Config::set('LaravelLogger.logSuccessfulLogout', true);
        Config::set('LaravelLogger.disableRoutes', false);
        Config::set('LaravelLogger.loggerDatatables', false);
        Config::set('LaravelLogger.enableSubMenu', true);
        Config::set('LaravelLogger.enableDrillDown', true);
        Config::set('LaravelLogger.logDBActivityLogFailuresToFile', true);
        Config::set('LaravelLogger.enablePackageFlashMessageBlade', true);
        Config::set('LaravelLogger.loggerBladeExtended', 'layouts.app');
        Config::set('LaravelLogger.bootstapVersion', '4');
        Config::set('LaravelLogger.bootstrapCardClasses', '');
        Config::set('LaravelLogger.bladePlacement', 'yield');
        Config::set('LaravelLogger.bladePlacementCss', 'template_linked_css');
        Config::set('LaravelLogger.bladePlacementJs', 'footer_scripts');
        Config::set('LaravelLogger.enablejQueryCDN', true);
        Config::set('LaravelLogger.JQueryCDN', 'https://code.jquery.com/jquery-3.2.1.slim.min.js');
        Config::set('LaravelLogger.enableBootstrapCssCDN', true);
        Config::set('LaravelLogger.bootstrapCssCDN', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        Config::set('LaravelLogger.enableBootstrapJsCDN', true);
        Config::set('LaravelLogger.bootstrapJsCDN', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
        Config::set('LaravelLogger.enablePopperJsCDN', true);
        Config::set('LaravelLogger.popperJsCDN', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js');
        Config::set('LaravelLogger.enableFontAwesomeCDN', true);
        Config::set('LaravelLogger.fontAwesomeCDN', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        Config::set('LaravelLogger.enableLiveSearch', true);
    }
}
