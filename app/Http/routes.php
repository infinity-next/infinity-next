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
Route::group([
	'namespace' => 'Auth',
	'prefix'    => 'cp',
], function()
{
	// Simple /cp/ requests go directly to /cp/home
	Route::get('/', 'HomeController@getIndex');
	
	Route::controllers([
		// /cp/auth handles sign-ins and registrar work.
		'auth'     => 'AuthController',
		// /cp/home is a landing page.
		'home'     => 'HomeController',
		// /cp/password handles password resets and recovery.
		'password' => 'PasswordController',
	]);
	
	// /cp/donate is a Stripe cashier system for donations.
	if (env('CONTRIB_ENABLED', false))
	{
		Route::controller('donate', 'DonateController');
	}
	
	Route::group([
		'namespace' => 'Site',
		'prefix'    => 'site',
	], function()
	{
		// Simple /cp/ requests go directly to /cp/home
		Route::get('/', 'SiteController@getIndex');
		
		Route::controllers([
			'config' => 'ConfigController',
		]);
	});
	
	Route::group([
		'namespace' => 'Board',
		'prefix'    => 'board',
	], function()
	{
		
	});
});

/*
| Contribution (contribute)
| Only enabled if CONTRIB_ENABLED is set to TRUE.
| Opens the fundraiser page.
*/
if (env('CONTRIB_ENABLED', false))
{
	Route::get('contribute', 'ContributeController@index');
}


/*
| Board (/anything/)
| A catch all. Used to load boards.
*/
Route::group([
	'namespace' => 'Board',
	'prefix'    => '{board}',
	'where'     => ['board' => '[a-z]{1,31}'],
], function()
{
	// /board/file/ requests (for thumbnails & files) goes to the FileController.
	Route::group([
		'prefix'    => 'file',
	], function()
	{
		Route::get('/', 'FileController@getIndex');
		
		Route::get('{hash}/{filename}', 'FileController@getFile')
			->where([
				'hash'     => "[a-f0-9]{32}",
			]);
		
		Route::get('thumb/{hash}/{filename}', 'FileController@getThumbnail')
			->where([
				'hash'     => "[a-f0-9]{32}",
			]);
	});
	
	Route::group([
		'prefix'    => 'post/{post}',
		'where'     => ['{post}' => '[1-9]\d*'],
	], function()
	{
		Route::controller('', 'PostController');
	});
	
	// Pushes simple /board/ requests to their index page.
	Route::any('/',    'BoardController@getIndex');
	
	// Routes /board/1 to an index page for a specific pagination point.
	Route::get('{id}', 'BoardController@getIndex')->where(['id' => '[0-9]+']);
	
	// More complicated /board/view requests.
	Route::controller('', 'BoardController');
});