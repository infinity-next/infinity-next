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
	'namespace' => 'Panel',
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
		'namespace' => 'Boards',
		'prefix'    => 'boards',
	], function()
	{
		Route::get('/', 'BoardsController@getIndex');
		Route::get('create', 'BoardsController@getCreate');
		Route::put('create', 'BoardsController@putCreate');
	});
	
	Route::group([
		'namespace' => 'Boards',
		'prefix'    => 'board',
	], function()
	{
		Route::controller('{board}', 'ConfigController');
	});
	
	Route::group([
		'namespace' => 'Site',
		'prefix'    => 'site',
	], function()
	{
		Route::get('/', 'SiteController@getIndex');
		Route::get('phpinfo', 'SiteController@getPhpinfo');
		
		Route::controllers([
			'config' => 'ConfigController',
		]);
	});
	
	Route::group([
		'namespace' => 'Users',
		'prefix'    => 'users',
	], function()
	{
		Route::get('/', 'UsersController@getIndex');
	});
	
	Route::group([
		'namespace' => 'Roles',
		'prefix'    => 'roles',
	], function()
	{
		Route::controller('permissions/{role}', 'PermissionsController');
		Route::get('permissions', 'RolesController@getPermissions');
	});
	
});


/*
| Page Controllers
| Catches specific strings to route to static content.
*/
if (env('CONTRIB_ENABLED', false))
{
	Route::get('contribute', 'PageController@getContribute');
}


/*
| Board (/anything/)
| A catch all. Used to load boards.
*/
Route::group([
	'namespace' => 'Board',
	'prefix'    => '{board}',
], function()
{
	/*
	| Board Stylesheet Request
	*/
	Route::get('style.css', 'BoardController@getStylesheet');
	
	/*
	| Board Attachment Routes (Files)
	*/
	Route::group([
		'prefix' => 'file',
	], function()
	{
		Route::get('/', 'FileController@getIndex');
		
		Route::get('{hash}/{filename}', 'FileController@getFile')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
		
		Route::get('thumb/{hash}/{filename}', 'FileController@getThumbnail')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
	});
	
	
	/*
	| Board Post Routes (Modding)
	*/
	Route::group([
		'prefix' => 'post/{post}',
		'where'  => ['{post}' => '[1-9]\d*'],
	], function()
	{
		Route::controller('', 'PostController');
	});
	
	
	
	/*
	| Legacy Routes
	*/
	if (env('LEGACY_ROUTES', false))
	{
		Route::any('/index.html', function(App\Board $board) {
			return redirect("{$board->board_uri}");
		});
		Route::any('/catalog.html', function(App\Board $board) {
			return redirect("{$board->board_uri}/catalog");
		});
		Route::any('/{id}.html', function(App\Board $board, $id) {
			return redirect("{$board->board_uri}/{$id}");
		})->where(['id' => '[0-9]+']);
		Route::any('/res/{id}.html', function(App\Board $board, $id) {
			return redirect("{$board->board_uri}/thread/{$id}");
		})->where(['id' => '[0-9]+']);
		Route::any('/res/{id}+{last}.html', function(App\Board $board, $id, $last) {
			return redirect("{$board->board_uri}/thread/{$id}/{$last}");
		})->where(['id' => '[0-9]+', 'last' => '[0-9]+']);
	}
	
	
	/*
	| Board Controller Routes
	| These are greedy and will redirect before others, so make sure they stay last.
	*/
	// Pushes simple /board/ requests to their index page.
	Route::any('/', 'BoardController@getIndex');
	
	// Loads the catalog.
	Route::get('catalog', 'BoardController@getCatalog');
	
	// Routes /board/1 to an index page for a specific pagination point.
	Route::get('{id}', 'BoardController@getIndex')
		->where(['id' => '[0-9]+']);
	
	// More complicated /board/view requests.
	Route::controller('', 'BoardController');
});