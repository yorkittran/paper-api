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

Route::get('task', 'Api\TaskController@index')->name('task.index');
Route::post('task', 'Api\TaskController@store')->name('task.store');
Route::delete('task/{task}', 'Api\TaskController@delete')->name('task.delete');
Route::match(['put', 'patch'], 'task/approve/{task}', 'Api\TaskController@approve')->name('task.approve');
Route::match(['put', 'patch'], 'task/update/{task}', 'Api\TaskController@update')->name('task.update');
Route::match(['put', 'patch'], 'task/commit/{task}', 'Api\TaskController@commit')->name('task.commit');
Route::match(['put', 'patch'], 'task/evaluate/{task}', 'Api\TaskController@evaluate')->name('task.evaluate');
