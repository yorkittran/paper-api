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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('login', 'Api\AuthController@login');
Route::post('logout', 'Api\AuthController@logout')->middleware('auth:api');

Route::get('group', 'Api\GroupController@index')->name('group.index');
Route::post('group', 'Api\GroupController@store')->name('group.store');
Route::delete('group/{group}', 'Api\GroupController@delete')->name('group.delete');
Route::match(['put', 'patch'], 'group/{group}', 'Api\GroupController@update')->name('group.update');

Route::get('user', 'Api\UserController@index')->name('user.index');
Route::post('user', 'Api\UserController@store')->name('user.store');
Route::get('user/{user}', 'Api\UserController@show')->name('user.index');
Route::delete('user/{user}', 'Api\UserController@delete')->name('user.delete');
Route::match(['put', 'patch'], 'user/{user}', 'Api\UserController@update')->name('user.update');
Route::post('user/usersInGroup', 'Api\UserController@usersInGroup')->name('user.usersInGroup');

Route::resource('task', 'Api\TaskController');
