<?php namespace App\Providers;

use App\Board;
use App\Post;

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
		$router->pattern('board', Board::URI_PATTERN);
		$router->pattern('id',   '[0-9]\d*');
		
		$router->model('ban',    '\App\Ban');
		$router->model('board',  '\App\Board');
		$router->model('post',   '\App\Post');
		$router->model('report', '\App\Report');
		$router->model('role',   '\App\Role');
		
		$router->bind('user', function($value, $route) {
			if (is_numeric($value))
			{
				return \App\User::find($value);
			}
			else if (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches))
			{
				return \App\User::find($matches['id']);
			}
		});
		
		$router->bind('role', function($value, $route) {
			if (is_numeric($value))
			{
				return \App\Role::find($value);
			}
			else if (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches))
			{
				return \App\Role::find($matches['id']);
			}
		});
		
		$router->bind('post_id', function($value, $route) {
			$board = $route->getParameter('board');
			
			if (is_numeric($value) && $board instanceof Board)
			{
				return $board->getThreadByBoardId($value);
			}
		});
		
		
		// Binds a matched instance of a {board} as a singleton instance.
		$router->matched(function($route, $request) {
			// Binds the board to the application if it exists.
			$board = $route->getParameter('board');
			
			if ($board instanceof Board && $board->exists)
			{
				$board->applicationSingleton = true;
				//$this->app->instance("\App\Board", $board);
				$this->app->singleton("\App\Board", function($app) use ($board) {
					return $board->load([
						'assets',
						'assets.storage',
						'settings'
					]);
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
