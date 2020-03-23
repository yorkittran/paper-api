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
    Route::get('group/{group}', 'Api\GroupController@show')->name('group.show');
    Route::post('group', 'Api\GroupController@store')->name('group.store');
    Route::delete('group/{group}', 'Api\GroupController@destroy')->name('group.destroy');
    Route::match(['put', 'patch'], 'group/{group}', 'Api\GroupController@update')->name('group.update');

    // UserController
    Route::post('user', 'Api\UserController@store')->name('user.store');
    Route::get('user/members', 'Api\UserController@members')->name('user.members');
    Route::get('user/managers', 'Api\UserController@managers')->name('user.managers');
    Route::delete('user/{user}', 'Api\UserController@destroy')->name('user.destroy');
    Route::match(['put', 'patch'], 'user/{user}', 'Api\UserController@update')->name('user.update');
});

Route::middleware('auth.role:Admin,Manager')->group(function () {
    // UserController
    Route::get('user', 'Api\UserController@index')->name('user.index');
    Route::get('user/{user}', 'Api\UserController@show')->name('user.show');

    // TaskController
    Route::get('task', 'Api\TaskController@index')->name('task.index');
    Route::match(['put', 'patch'], 'task/approve/{task}', 'Api\TaskController@approve')->name('task.approve');
    Route::match(['put', 'patch'], 'task/reject/{task}', 'Api\TaskController@reject')->name('task.reject');
    Route::match(['put', 'patch'], 'task/evaluate/{task}', 'Api\TaskController@evaluate')->name('task.evaluate');
});

Route::middleware('auth.role:Manager,Member')->group(function () {
    // TaskController
    Route::get('task/given', 'Api\TaskController@given')->name('task.given');
    Route::match(['put', 'patch'], 'task/commit/{task}', 'Api\TaskController@commit')->name('task.commit');
});

// UserController
Route::get('profile', 'Api\UserController@profile')->name('user.profile');

// TaskController
Route::get('task/{task}', 'Api\TaskController@show')->name('task.show');
Route::post('task', 'Api\TaskController@store')->name('task.store');
Route::delete('task/{task}', 'Api\TaskController@destroy')->name('task.destroy');
Route::match(['put', 'patch'], 'task/update/{task}', 'Api\TaskController@update')->name('task.update');
