<?php

use Illuminate\Http\Request;

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


// Routes for the authentication process(Register, login, authenticated user info, logout) 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('register', 'AuthController@register');
});

// Admin Gets all employees
Route::get('employees', 'AdminController@getEmployees')->middleware('auth','admin');

// Admin get the details of a specific employee
Route::get('employee/{id}', 'AdminController@getEmployee')->middleware('auth','admin');

// Admin adds a new employee
Route::post('addEmployee', 'AdminController@addEmployee')->middleware('auth','admin');

// Admin updates the info of a specific employee
Route::put('updateEmployee/{id}', 'AdminController@updateEmployee')->middleware('auth','admin');

// Admin deletes a specific employee
Route::delete('deleteEmployee/{id}', 'AdminController@deleteEmployee')->middleware('auth','admin');

// Admin gets the payment dates for the remainder of this year with the corresponding amount to be paid each month
Route::get('summary', 'AdminController@summary')->middleware('auth','admin');

// Admin decides if the bonus will start to be added from next month or as default the bonus is already added and calculated in the payements
Route::post('startBonus', 'AdminController@startBonus')->middleware('auth','admin');

