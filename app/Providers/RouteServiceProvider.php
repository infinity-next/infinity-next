<?php namespace App\Providers;

use App\Board;
use App\Page;
use App\Post;
use App\User;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		// Sets up our routing tokens.
		$router->pattern('attachment', '[0-9]\d*');
		$router->pattern('board',      Board::URI_PATTERN);
		$router->pattern('id',         '[0-9]\d*');

		$router->model('attachment', '\App\FileAttachment');
		$router->model('ban',        '\App\Ban');
		$router->model('board',      '\App\Board');
		$router->model('page',       '\App\Page');
		$router->model('post',       '\App\Post');
		$router->model('report',     '\App\Report');
		$router->model('role',       '\App\Role');

		$router->bind('user', function($value, $route)
		{
			if (is_numeric($value))
			{
				return User::find($value);
			}
			else if (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches))
			{
				return User::find($matches['id']);
			}
		});

		$router->bind('page', function($value, $route)
		{
			if (is_numeric($value))
			{
				return Page::find($value);
			}
			else if (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches))
			{
				return Page::find($matches['id']);
			}
		});

		$router->bind('page_title', function($value, $route)
		{
			$board = $route->getParameter('board');

			return Page::where([
				'board_uri' => $board instanceof Board ? $board->board_uri : null,
				'title' => $value,
			])->first();
		});

		$router->bind('board', function($value, $route)
		{
			$board = Board::getBoardForRouter(
				$this->app,
				$value
			);

			if ($board)
			{
				$board->applicationSingleton = true;
				return $board;
			}

			return abort(404);
		});

		$router->bind('attachment', function($value, $route)
		{
			return \App\FileAttachment::where('is_deleted', false)
				->with('storage')
				->find($value);
		});

		$router->bind('role', function($value, $route)
		{
			if (is_numeric($value))
			{
				return \App\Role::find($value);
			}
			else if (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches))
			{
				return \App\Role::find($matches['id']);
			}
		});

		$router->bind('post_id', function($value, $route)
		{
			$board = $route->getParameter('board');

			if (!($board instanceof Board))
			{
				$board = $this->app->make("\App\Board");
			}

			if (is_numeric($value) && $board instanceof Board)
			{
				return $board->getThreadByBoardId($value);
			}
		});

		// Binds a matched instance of a {board} as a singleton instance.
		$router->matched(function($route, $request)
		{
			$board = $route->getParameter('board');

			if ($board instanceof Board && $board->exists)
			{
				// Binds the board to the application if it exists.
				$this->app->singleton("\App\Board", function($app) use ($board) {
					return $board;
				});
			}

			// Binds the post to the application if it exists.
			$post = $route->getParameter('post_id');

			if ($post instanceof Post && $post->exists)
			{
				$route->setParameter('post', $post);
				//$this->app->instance("\App\Post", $post);
				$this->app->singleton("\App\Post", function($app) use ($post) {
					return $post;
				});
			}
		});


		parent::boot($router);
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
