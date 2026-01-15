![Laravel Logger](https://github-project-images.s3-us-west-2.amazonaws.com/laravel-blocker/laravel-logger-logo.png)

# Laravel Activity Logger

Laravel logger is an activity event logger for your Laravel or Lumen application. It comes out the box with ready to use with dashboard to view your activity. Laravel logger can be added as a middleware or called through a trait. Easily have an Activity Log. This package is easily configurable and customizable. Supports Laravel 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6, 7, 8 and 9+

[![Latest Stable Version](https://poser.pugx.org/jeremykenedy/laravel-logger/v/stable)](https://packagist.org/packages/jeremykenedy/laravel-logger)
[![Total Downloads](https://poser.pugx.org/jeremykenedy/laravel-logger/downloads)](https://packagist.org/packages/jeremykenedy/laravel-logger)
<a href="https://styleci.io/repos/109630720">
<img src="https://styleci.io/repos/109630720/shield?branch=master" alt="StyleCI" style="border-radius: 3px;">
</a>
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jeremykenedy/laravel-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jeremykenedy/laravel-logger/?branch=master)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

#### Table of contents

- [Features](#features)
- [Requirements](#requirements)
- [Integrations](#integrations)
- [Laravel Installation Instructions](#laravel-installation-instructions)
- [Lumen Installation Instructions](#lumen-installation-instructions)
- [Configuration](#configuration)
  - [Environment File](#environment-file)
- [Usage](#usage)
  - [Middleware Usage](#middleware-usage)
  - [Trait Usage](#trait-usage)
- [Routes](#routes)
- [Search & Filtering](#search--filtering)
- [Screenshots](#screenshots)
- [File Tree](#file-tree)
- [Opening an Issue](#opening-an-issue)
- [License](#license)

### Features

| Laravel Activity Logger Features                                                  |
| :-------------------------------------------------------------------------------- |
| Logs login page visits                                                            |
| Logs user logins                                                                  |
| Logs user logouts                                                                 |
| Routing Events can recording using middleware                                     |
| Records activity timestamps                                                       |
| Records activity description                                                      |
| Records activity details (optional)                                               |
| Records model related to the activity (optional)                                  |
| Records activity user type with crawler detection                                 |
| Records activity Method                                                           |
| Records activity Route                                                            |
| Records activity Ip Address                                                       |
| Records activity User Agent                                                       |
| Records activity Browser Language                                                 |
| Records activity referrer                                                         |
| Customizable activity model                                                       |
| Activity panel dashboard                                                          |
| Individual activity drilldown report dashboard                                    |
| Activity Drilldown looks up Id Address meta information                           |
| Activity Drilldown shows user roles if enabled                                    |
| Activity Drilldown shows associated user events                                   |
| Activity log can be cleared, restored, and destroyed using eloquent softdeletes   |
| Cleared activity logs can be viewed and have drilldown ability                    |
| Uses font awesome, cdn assets can be optionally called in configuration           |
| Uses [Geoplugin API](http://www.geoplugin.com/) for drilldown IP meta information |
| Uses Language localization files                                                  |
| Lots of [configuration](#configuration) options                                   |
| **NEW: Date filtering** - Filter activities by date range or predefined periods   |
| **NEW: Export functionality** - Export activities to CSV, JSON, and Excel formats |
| **NEW: Enhanced search** - Improved search with date filtering support            |
| **NEW: Performance optimizations** - Better handling of large datasets            |

### Requirements

- [Laravel 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 6, and 7+](https://laravel.com/docs/installation)
- [jaybizzle/laravel-crawler-detect](https://github.com/JayBizzle/Laravel-Crawler-Detect) included dependency in composer.json (for crawler detection)

### :film_strip: Video Tour

If you'd prefer a more visual review of this package, please watch this video on Laravel Package Tutorial.

[<img src="https://img.youtube.com/vi/mHLSv9XhTuk/0.jpg" width="350">](https://youtu.be/mHLSv9XhTuk)

### Integrations

Laravel logger can work out the box with or without the following roles packages:

- [jeremykenedy/laravel-roles](https://github.com/jeremykenedy/laravel-roles)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [Zizaco/entrust](https://github.com/Zizaco/entrust)
- [romanbican/roles](https://github.com/romanbican/roles)
- [ultraware/roles](https://github.com/ultraware/roles)

### Laravel Installation Instructions

1. From your projects root folder in terminal run:

```bash
    composer require jeremykenedy/laravel-logger
```

2. Register the package

- Laravel 5.5 and up
  Uses package auto discovery feature, no need to edit the `config/app.php` file.

- Laravel 5.4 and below
  Register the package with laravel in `config/app.php` under `providers` with the following:

```php
    'providers' => [
        jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider::class,
    ];
```

3. Run the migration to add the table to record the activities to:

```php
    php artisan migrate
```

- Note: If you want to specify a different table or connection make sure you update your `.env` file with the needed configuration variables.

4. Optionally Update your `.env` file and associated settings (see [Environment File](#environment-file) section)

5. Optionally publish the packages views, config file, assets, and language files by running the following from your projects root folder:

```bash
    php artisan vendor:publish --tag=LaravelLogger
```

### Lumen Installation Instructions

##### This installs laravel-logger without the GUI

1. From your projects root folder in terminal run:

```bash
    composer require jeremykenedy/laravel-logger
```

2. Register the package

Register the package with laravel in `bootstrap/app.php` with the following:

```php
    $app->register(\Jaybizzle\LaravelCrawlerDetect\LaravelCrawlerDetectServiceProvider::class);
    $app->configure('laravel-logger');
    $app->register(\jeremykenedy\LaravelLogger\LaravelLoggerServiceProvider::class);
    $app->routeMiddleware(['activity' => \jeremykenedy\LaravelLogger\App\Http\Middleware\LogActivity::class, ]);
```

3. Copy the configuration file [laravel-logger.php](src/config/laravel-logger.php) to your `config/` directory

##### Set LARAVEL_LOGGER_DISABLE_ROUTES=true in your .env file!

4. Run the migration to add the table to record the activities to:

```php
    php artisan migrate
```

- Note: If you want to specify a different table or connection make sure you update your `.env` file with the needed configuration variables.

5. Optionally Update your `.env` file and associated settings (see [Environment File](#environment-file) section)

### Configuration

Laravel Activity Logger can be configured in directly in `/config/laravel-logger.php` if you published the assets.
Or you can variables to your `.env` file.

##### Environment File

Here are the `.env` file variables available:

```dotenv
LARAVEL_LOGGER_DATABASE_CONNECTION=mysql
LARAVEL_LOGGER_DATABASE_TABLE=laravel_logger_activity
LARAVEL_LOGGER_ROLES_ENABLED=true
LARAVEL_LOGGER_ROLES_MIDDLWARE=role:admin
LARAVEL_LOGGER_MIDDLEWARE_ENABLED=true
LARAVEL_LOGGER_MIDDLEWARE_EXCEPT=
LARAVEL_LOGGER_ACTIVITY_MODEL=jeremykenedy\LaravelLogger\App\Models\Activity
LARAVEL_LOGGER_USER_MODEL=App\User
LARAVEL_LOGGER_USER_ID_FIELD=id
LARAVEL_LOGGER_DISABLE_ROUTES=false
LARAVEL_LOGGER_PAGINATION_ENABLED=true
LARAVEL_LOGGER_CURSOR_PAGINATION_ENABLED=false
LARAVEL_LOGGER_PAGINATION_PER_PAGE=25
LARAVEL_LOGGER_DATATABLES_ENABLED=true
LARAVEL_LOGGER_ENABLE_SEARCH=true
LARAVEL_LOGGER_SEARCH_FIELDS=description,user,method,route,ip
LARAVEL_LOGGER_ENABLE_DATE_FILTERING=true
LARAVEL_LOGGER_ENABLE_EXPORT=true
LARAVEL_LOGGER_DASHBOARD_MENU_ENABLED=true
LARAVEL_LOGGER_DASHBOARD_DRILLABLE=true
LARAVEL_LOGGER_LOG_RECORD_FAILURES_TO_FILE=true
LARAVEL_LOGGER_FLASH_MESSAGE_BLADE_ENABLED=true
LARAVEL_LOGGER_LAYOUT=layouts.app
LARAVEL_LOGGER_BOOTSTRAP_VERSION=4
LARAVEL_LOGGER_BLADE_PLACEMENT=stack                    #option: yield or stack
LARAVEL_LOGGER_BLADE_PLACEMENT_CSS=css-header           #placement name
LARAVEL_LOGGER_BLADE_PLACEMENT_JS=scripts-footer        #placement name
LARAVEL_LOGGER_JQUERY_CDN_ENABLED=true
LARAVEL_LOGGER_JQUERY_CDN_URL=https://code.jquery.com/jquery-2.2.4.min.js
LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_ENABLED=true
LARAVEL_LOGGER_BOOTSTRAP_CSS_CDN_URL=https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css
LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_ENABLED=true
LARAVEL_LOGGER_BOOTSTRAP_JS_CDN_URL=https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js
LARAVEL_LOGGER_POPPER_JS_CDN_ENABLED=true
LARAVEL_LOGGER_POPPER_JS_CDN_URL=https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js
LARAVEL_LOGGER_FONT_AWESOME_CDN_ENABLED=true
LARAVEL_LOGGER_FONT_AWESOME_CDN_URL=https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css
```

### Usage

##### Middleware Usage

Events for laravel authentication scaffolding are listened for as providers and are enabled via middleware.
You can add events to your routes and controllers via the middleware:

```php
activity
```

Example to start recording page views using middlware in `web.php`:

```php
Route::group(['middleware' => ['web', 'activity']], function () {
    Route::get('/', 'WelcomeController@welcome')->name('welcome');
});
```

This middlware can be enabled/disabled in the configuration settings.

##### Trait Usage

Events can be recorded directly by using the trait.
When using the trait you can customize the event description.

To use the trait:

1. Include the call in the head of your class file:

   ```php
       use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
   ```

2. Include the trait call in the opening of your class:

   ```php
       use ActivityLogger;
   ```

3. You can record the activity by calling the traits method:

   ```
       ActivityLogger::activity("Logging this activity.");
   ```

   Or as bellow to include extended activity details:

   ```
       ActivityLogger::activity("Logging this activity.", "Additional activity details.");
   ```

   Or even including the model related to the activity:

   ```
       ActivityLogger::activity("Logging this activity.", "Additional activity details.", ["id" => 1, "model" => "App\Models\User"]);
   ```

### Routes

##### Laravel Activity Dashbaord Routes

- `/activity` - Main activity dashboard
- `/activity/cleared` - Cleared activities dashboard
- `/activity/log/{id}` - Individual activity details
- `/activity/cleared/log/{id}` - Individual cleared activity details
- `/activity/export` - Export activities (supports CSV, JSON, Excel formats)

#### Custom package routes

If you wish to change the route paths, names or other options you can disable the default routes in your `.env` file by setting

```dotenv
LARAVEL_LOGGER_DISABLE_ROUTES=true
```

If you are on an existing install, you will also need update your `laravel-logger.php` config file to add the config option:

```php
'disableRoutes' => env('LARAVEL_LOGGER_DISABLE_ROUTES', false),
```

You can then add the routes directly to your application's `routes/web.php` file, and customise as required.

```php
Route::group(['prefix' => 'activity', 'namespace' => 'jeremykenedy\LaravelLogger\App\Http\Controllers', 'middleware' => ['web', 'auth', 'activity']], function () {

    // Dashboards
    Route::get('/', 'LaravelLoggerController@showAccessLog')->name('activity');
    Route::get('/cleared', ['uses' => 'LaravelLoggerController@showClearedActivityLog'])->name('cleared');

    // Drill Downs
    Route::get('/log/{id}', 'LaravelLoggerController@showAccessLogEntry');
    Route::get('/cleared/log/{id}', 'LaravelLoggerController@showClearedAccessLogEntry');

    // Export
    Route::get('/export', ['uses' => 'LaravelLoggerController@exportActivityLog'])->name('export-activity');

    // Forms
    Route::delete('/clear-activity', ['uses' => 'LaravelLoggerController@clearActivityLog'])->name('clear-activity');
    Route::delete('/destroy-activity', ['uses' => 'LaravelLoggerController@destroyActivityLog'])->name('destroy-activity');
    Route::post('/restore-log', ['uses' => 'LaravelLoggerController@restoreClearedActivityLog'])->name('restore-activity');
});
```

### Search & Filtering

Enhanced search functionality with date filtering and export capabilities.

### High Performance Paginator

When dealing with millions activity records, default behavior of not paginate records or [Laravel's paginator](https://laravel.com/docs/pagination#paginating-eloquent-results) (enabled `LARAVEL_LOGGER_PAGINATION_ENABLED=true`) may lead to huge performance penalties. For that use case you may set `LARAVEL_LOGGER_CURSOR_PAGINATION_ENABLED=true` to enable [Laravel's Cursor Pagination](https://laravel.com/docs/pagination#cursor-pagination) feature. This will heavily improve Laravel Logger page loading time. If you choose to do so it's advisable to read [Cursor vs. Offset Pagination](https://laravel.com/docs/pagination#cursor-vs-offset-pagination) section on Laravel's documentation to get acquainted with Cursor Pagination limitations.

##### .env file

add these configurations to your .env file to control the logging search and filtering

```
LARAVEL_LOGGER_ENABLE_SEARCH=true
LARAVEL_LOGGER_SEARCH_FIELDS=description,user,method,route,ip
LARAVEL_LOGGER_ENABLE_DATE_FILTERING=true
LARAVEL_LOGGER_ENABLE_EXPORT=true
```

##### Date Filtering

Filter activities by:

- Date range (from/to dates)
- Predefined periods (today, yesterday, last 7/30 days, last 3/6 months, last year)

##### Export Functionality

Export activities in multiple formats:

- CSV format with proper headers
- JSON format with structured data
- Excel format (.xlsx)

##### Usage Examples

```
# Filter by date range
/activity?date_from=2024-01-01&date_to=2024-12-31

# Filter by predefined period
/activity?period=last_7_days

# Export filtered data
/activity/export?format=csv&period=last_30_days
/activity/export?format=json&date_from=2024-01-01
```

### Screenshots

![dashboard](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/1-dashboard.jpg)
![drilldown](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/2-drilldown.jpg)
![confirm-clear](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/3-confirm-clear.jpg)
![log-cleared-msg](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/4-log-cleared-msg.jpg)
![cleared-log](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/5-cleared-log.jpg)
![confirm-restore](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/5-confirm-restore.jpg)
![confirm-destroy](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/6-confirm-destroy.jpg)
![success-destroy](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/7-success-destroy.jpg)
![success-restored](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/8-success-restored.jpg)
![cleared-drilldown](https://s3-us-west-2.amazonaws.com/github-project-images/laravel-logger/9-cleared-drilldown.jpg)

### File Tree

```bash
├── .env.travis
├── .gitignore
├── .travis.yml
├── CODE_OF_CONDUCT.md
├── LICENSE
├── README.md
├── composer.json
└── src
    ├── .env.example
    ├── LaravelLoggerServiceProvider.php
    ├── app
    │   ├── Http
    │   │   ├── Controllers
    │   │   │   └── LaravelLoggerController.php
    │   │   ├── Middleware
    │   │   │   └── LogActivity.php
    │   │   └── Traits
    │   │       ├── ActivityLogger.php
    │   │       ├── IpAddressDetails.php
    │   │       └── UserAgentDetails.php
    │   ├── Listeners
    │   │   ├── LogAuthenticated.php
    │   │   ├── LogAuthenticationAttempt.php
    │   │   ├── LogFailedLogin.php
    │   │   ├── LogLockout.php
    │   │   ├── LogPasswordReset.php
    │   │   ├── LogSuccessfulLogin.php
    │   │   └── LogSuccessfulLogout.php
    │   ├── Logic
    │   │   └── helpers.php
    │   └── Models
    │       └── Activity.php
    ├── config
    │   └── laravel-logger.php
    ├── database
    │   └── migrations
    │       └── 2017_11_04_103444_create_laravel_logger_activity_table.php
    ├── resources
    │   ├── lang
    │   │   ├── de
    │   │   │   └── laravel-logger.php
    │   │   └── en
    │   │       └── laravel-logger.php
    │   └── views
    │       ├── forms
    │       │   ├── clear-activity-log.blade.php
    │       │   ├── delete-activity-log.blade.php
    │       │   └── restore-activity-log.blade.php
    │       ├── logger
    │       │   ├── activity-log-cleared.blade.php
    │       │   ├── activity-log-item.blade.php
    │       │   ├── activity-log.blade.php
    │       │   └── partials
    │       │       └── activity-table.blade.php
    │       ├── modals
    │       │   └── confirm-modal.blade.php
    │       ├── partials
    │       │   ├── form-search.blade.php
    │       │   ├── form-status.blade.php
    │       │   ├── scripts.blade.php
    │       │   └── styles.blade.php
    │       └── scripts
    │           ├── add-title-attribute.blade.php
    │           ├── clickable-row.blade.php
    │           ├── confirm-modal.blade.php
    │           ├── datatables.blade.php
    │           └── tooltip.blade.php
    └── routes
        └── web.php
```

- Tree command can be installed using brew: `brew install tree`
- File tree generated using command `tree -a -I '.git|node_modules|vendor|storage|tests'`

### Opening an Issue

Before opening an issue there are a couple of considerations:

- You are all awesome!
- **Read the instructions** and make sure all steps were _followed correctly_.
- **Check** that the issue is not _specific to your development environment_ setup.
- **Provide** _duplication steps_.
- **Attempt to look into the issue**, and if you _have a solution, make a pull request_.
- **Show that you have made an attempt** to _look into the issue_.
- **Check** to see if the issue you are _reporting is a duplicate_ of a previous reported issue.
- **Following these instructions show me that you have tried.**
- If you have a questions send me an email to jeremykenedy@gmail.com
- Need some help, I can do my best on Slack: https://opensourcehelpgroup.slack.com
- Please be considerate that this is an open source project that I provide to the community for FREE when opening an issue.

### License

Laravel-logger is licensed under the MIT license. Enjoy!
