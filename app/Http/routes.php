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
		'auth'     => 'Auth\AuthController',
		'home'     => 'Auth\HomeController',
		'password' => 'Auth\PasswordController',
	]);
	
	if (env('CONTRIB_ENABLED', false))
	{
		Route::controllers([
			'donate'   => 'Auth\DonateController',
		]);
	}
});

if (env('CONTRIB_ENABLED', false))
{
	Route::get('contribute', 'ContributeController@index');
	Route::get('contribute/donate', 'ContributeController@donate');
}

Route::group([
	'prefix' => '{board}',
	'where'  => ['board' => '[a-z]{1,31}'],
], function()
{
	Route::controller('', 'Board\BoardController');
});