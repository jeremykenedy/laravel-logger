<?php

/*
|--------------------------------------------------------------------------
| Laravel Logger Web Routes
|--------------------------------------------------------------------------
|
*/

Route::group(['prefix' => 'activity','namespace' => 'jeremykenedy\LaravelLogger\App\Http\Controllers','middleware' => ['web', 'auth', 'activity']], function() {

    Route::get('/', 'LaravelLoggerController@showAccessLog')->name('activity');
    Route::get('/log/{id}', 'LaravelLoggerController@showAccessLogEntry');

});
