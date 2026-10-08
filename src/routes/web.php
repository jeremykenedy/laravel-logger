<?php

use Illuminate\Support\Facades\Route;
use jeremykenedy\LaravelLogger\App\Http\Middleware\ValidateActivityFilters;

/*
|--------------------------------------------------------------------------
| Laravel Logger Web Routes
|--------------------------------------------------------------------------
|
*/

Route::group(['prefix' => 'activity', 'namespace' => 'jeremykenedy\LaravelLogger\App\Http\Controllers', 'middleware' => ['web', 'auth', 'activity']], function (): void {
    // Dashboards
    Route::get('/', 'LaravelLoggerController@showAccessLog')->middleware(ValidateActivityFilters::class)->name('activity');
    Route::get('/cleared', ['uses' => 'LaravelLoggerController@showClearedActivityLog'])->middleware(ValidateActivityFilters::class)->name('cleared');

    // Drill Downs
    Route::get('/log/{id}', 'LaravelLoggerController@showAccessLogEntry')->middleware(ValidateActivityFilters::class);
    Route::get('/cleared/log/{id}', 'LaravelLoggerController@showClearedAccessLogEntry')->middleware(ValidateActivityFilters::class);

    // Forms
    Route::delete('/clear-activity', ['uses' => 'LaravelLoggerController@clearActivityLog'])->name('clear-activity');
    Route::delete('/destroy-activity', ['uses' => 'LaravelLoggerController@destroyActivityLog'])->name('destroy-activity');
    Route::post('/restore-log', ['uses' => 'LaravelLoggerController@restoreClearedActivityLog'])->name('restore-activity');

    // LiveSearch
    Route::post('/live-search', ['uses' => 'LaravelLoggerController@liveSearch'])->name('liveSearch');

    // Export functionality
    Route::get('/export', ['uses' => 'LaravelLoggerController@exportActivityLog'])->middleware(ValidateActivityFilters::class)->name('export-activity');
});
