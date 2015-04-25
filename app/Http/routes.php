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
	Route::any('/', 'Auth\HomeController@getIndex');
	
	Route::controllers([
		'home'     => 'Auth\HomeController',
		'auth'     => 'Auth\AuthController',
		'password' => 'Auth\PasswordController',
	]);
});

Route::get('contribute', 'ContributeController@index');
Route::get('contribute/donate', 'ContributeController@donate');

Route::post('{board}/thread/{thread}', 'BoardController@getThread');
Route::post('{board}/post/{thread}', 'BoardController@postThread');
Route::post('{board}/post', 'BoardController@postThread');
Route::post('{board}', 'BoardController@postThread');
Route::get('{board}/thread/{thread}', 'BoardController@getThread');
Route::get('{board}', 'BoardController@index');