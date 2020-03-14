<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'Api\AuthController@login');
// Route::post('logout', 'Api\AuthController@logout')->middleware('auth:api');

Route::middleware('auth.role:Admin')->group(function () {
    // GroupController
    Route::get('group', 'Api\GroupController@index')->name('group.index');
    Route::post('group', 'Api\GroupController@store')->name('group.store');
    Route::delete('group/{group}', 'Api\GroupController@delete')->name('group.delete');
    Route::match(['put', 'patch'], 'group/{group}', 'Api\GroupController@update')->name('group.update');

    // UserController
    Route::post('user', 'Api\UserController@store')->name('user.store');
    Route::delete('user/{user}', 'Api\UserController@delete')->name('user.delete');
    Route::match(['put', 'patch'], 'user/{user}', 'Api\UserController@update')->name('user.update');
});

Route::middleware('auth.role:Admin,Manager')->group(function () {
    // UserController
    Route::get('user', 'Api\UserController@index')->name('user.index');
    Route::get('user/{user}', 'Api\UserController@show')->name('user.show');
});


Route::resource('task', 'Api\TaskController');
