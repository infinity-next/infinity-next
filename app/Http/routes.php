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

Route::group([
	'prefix' => '/',
], function () {
	
	/*
	| Index route
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
		'namespace'  => 'Panel',
		'middleware' => 'App\Http\Middleware\VerifyCsrfToken',
		'prefix'     => 'cp',
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
		
		
		// /cp/adventure forwards you to a random board.
		if (true)
		{
			Route::controller('adventure', 'AdventureController');
		}
		
		
		Route::group([
			'prefix'    => 'banned',
		], function()
		{
			Route::get('/',                    'BannedController@getIndex');
			Route::get('/global',              'BannedController@getGlobalIndex');
			Route::get('/global/{ban}',        'BannedController@getBan');
			Route::get('/board/{board}',       'BannedController@getBoardIndex');
			Route::get('/board/{board}/{ban}', 'BannedController@getBan');
		});
		
		Route::group([
			'namespace' => 'Boards',
			'prefix'    => 'boards',
		], function()
		{
			Route::get('/', 'BoardsController@getIndex');
			
			Route::get('assets', 'BoardsController@getAssets');
			Route::get('config', 'BoardsController@getConfig');
			Route::get('staff',  'BoardsController@getStaff');
			
			Route::get('create', 'BoardsController@getCreate');
			Route::put('create', 'BoardsController@putCreate');
			
			Route::controller('reports', 'ReportsController');
			
			Route::group([
				'prefix'    => 'report',
			], function()
			{
				Route::get('{report}/dismiss',     'ReportsController@getDismiss');
				Route::get('{report}/dismiss-ip',  'ReportsController@getDismissIp');
				Route::get('{post}/dismiss-post',  'ReportsController@getDismissPost');
				Route::get('{report}/promote',     'ReportsController@getPromote');
				Route::get('{post}/promote-post',  'ReportsController@getPromotePost');
				Route::get('{report}/demote',     'ReportsController@getDemote');
				Route::get('{post}/demote-post',  'ReportsController@getDemotePost');
			});
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
		Route::get('contribute.json', 'API\PageController@getContribute');
	}
	
	
	/*
	| Board (/anything/)
	| A catch all. Used to load boards.
	*/
	Route::group([
		'prefix'    => '{board}',
	], function()
	{
		/*
		| Board Attachment Routes (Files)
		*/
		Route::group([
			'prefix'     => 'file',
			'middleware' => 'App\Http\Middleware\FileFilter',
			'namespace'  => 'Content',
		], function()
		{
			Route::get('{hash}/{filename}', 'ImageController@getImage')
				->where([
					'hash' => "[a-f0-9]{32}",
				]);
			
			Route::get('thumb/{hash}/{filename}', 'ImageController@getThumbnail')
				->where([
					'hash' => "[a-f0-9]{32}",
				]);
		});
		
		
		/*
		| Board Routes (Standard Requests)
		*/
		Route::group([
			'namespace' => 'Board',
		], function()
		{
			/*
			| Legacy Redirects
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
				});
				Route::any('/res/{id}.html', function(App\Board $board, $id) {
					return redirect("{$board->board_uri}/thread/{$id}");
				});
				Route::any('/res/{id}+{last}.html', function(App\Board $board, $id, $last) {
					return redirect("{$board->board_uri}/thread/{$id}/{$last}");
				})->where(['last' => '[0-9]+']);
			}
			
			
			/*
			| Board Post Routes (Modding)
			*/
			Route::group([
				'prefix' => 'post/{id}',
			], function()
			{
				Route::controller('', 'PostController');
			});
			
			
			/*
			| Board Controller Routes
			| These are greedy and will redirect before others, so make sure they stay last.
			*/
			// Pushes simple /board/ requests to their index page.
			Route::any('/', 'BoardController@getIndex');
			
			// Routes /board/1 to an index page for a specific pagination point.
			Route::get('{id}', 'BoardController@getIndex');
			
			
			// Get the catalog.
			Route::get('catalog', 'BoardController@getCatalog');
			
			// Get moderator logs
			Route::get('logs', 'BoardController@getLogs');
			
			// Get stylesheet
			Route::get('style.css', 'BoardController@getStylesheet');
			
			
			// Get single thread.
			Route::get('thread/{id}', 'BoardController@getThread');
			
			// Put new thread
			Route::put('thread', 'BoardController@putThread');
			
			// Put eply to thread.
			Route::put('thread/{id}', 'BoardController@putThread');
			
			// Redirect to a post.
			Route::get('post/{id}', 'BoardController@getPost');
			
			// Generate post preview.
			Route::any('post/preview', 'PostController@anyPreview');
			
			// Check if a file exists.
			Route::get('check-file', 'BoardController@getFile');
			
			// Handle a file upload.
			Route::post('upload-file', 'BoardController@putFile');
		});
		
		
		/*
		| Board API Routes (JSON)
		*/
		Route::group([
			'namespace' => "API\Board",
		], function()
		{
			// Gets the first page of a board.
			Route::any('index.json', 'BoardController@getIndex');
			
			// Gets index pages for the board.
			Route::get('{id}.json', 'BoardController@getIndex');
			
			// Gets all visible OPs on a board.
			Route::any('catalog.json', 'BoardController@getCatalog');
			
			// Get single thread.
			Route::get('thread/{id}.json', 'BoardController@getThread');
			
			// Get single post.
			Route::get('post/{id}.json', 'BoardController@getPost');
			
		});
	});
	
});

Route::group([
	'domain'     => 'api.{domain}.{tld}',
	'namespace'  => "API",
], function () {
	
	/*
	| Page Controllers
	| Catches specific strings to route to static content.
	*/
	if (env('CONTRIB_ENABLED', false))
	{
		Route::get('contribute', 'PageController@getContribute');
	}
	
});

Route::group([
	'domain'     => 'static.{domain}.{tld}',
	'namespace'  => 'Content',
], function() {
	
	Route::group([
		'prefix' => "image",
	], function()
	{
		Route::get('{hash}/{filename}', 'ImageController@getImage')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
		
		Route::get('thumb/{hash}/{filename}', 'ImageController@getThumbnail')
			->where([
				'hash' => "[a-f0-9]{32}",
			]);
	});
	
});