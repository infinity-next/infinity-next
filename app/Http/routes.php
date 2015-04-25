<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@getIndex');

/*
| Control Panel (cp)
| Anything having to deal with secure information goes here.
| This includes:
| - Registration, Login, and Account Recovery.
| - Contributor status.
| - Board creation, Board management, Volunteer management.
| - Top level site management.
*/
Route::group(['prefix' => 'cp'], function()
{
	Route::controllers([
		'home'     => 'Auth\HomeController',
		'auth'     => 'Auth\AuthController',
		'password' => 'Auth\PasswordController',
	]);
});

Route::get('contribute', 'ContributeController@index');
Route::get('contribute/donate', 'ContributeController@donate');

Route::get('{board}/thread/{thread}', 'BoardController@thread');
Route::post('{board}/post/{thread}', 'BoardController@post');
Route::post('{board}/post', 'BoardController@post');
Route::get('{board}', 'BoardController@index');