<?php

namespace App\Providers;

use App\Board;
use App\FileAttachment;
use App\Page;
use App\Post;
use App\Role;
use App\User;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $routeGroup = [
        'media'   => [
            'as'         => 'static.',
            'middleware' => 'media',
            'namespace'  => 'App\Http\Controllers\Media',
        ],

        'web'     => [
            'middleware' => 'web',
            'namespace'  => 'App\Http\Controllers',
        ],
        'content' => [
            'as'         => 'site.',
            'namespace'  => 'Content',
        ],
        'board' => [
            'prefix'     => "{board}",
            'as'         => 'board.',
            'namespace'  => 'Board',
        ],

        'panel' => [
            'as'         => 'panel.',
            'middleware' => ['web', 'panel'],
            'namespace'  => 'App\Http\Controllers\Panel',
        ],

        'api'     => 'API',
        'esi'     => 'ESI',
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->model('attachment', '\App\FileAttachment');
        $router->model('ban', '\App\Ban');
        $router->model('board', '\App\Board');
        $router->model('page', '\App\Page');
        //$router->model('post', '\App\Post');
        $router->model('report', '\App\Report');
        $router->model('role', '\App\Role');

        $router->bind('user', function ($value, $route) {
            if (is_numeric($value)) {
                return User::find($value);
            } elseif (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches)) {
                return User::find($matches['id']);
            }
        });

        $router->bind('page', function ($value, $route) {
            if (is_numeric($value)) {
                return Page::find($value);
            } elseif (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches)) {
                return Page::find($matches['id']);
            }
        });

        $router->bind('page_title', function ($value, $route) {
            $board = $route->getParameter('board');

            return Page::where([
                'board_uri' => $board instanceof Board ? $board->board_uri : null,
                'title' => $value,
            ])->first();
        });

        $router->bind('board', function ($value, $route) {
            $board = Board::getBoardForRouter(
                $this->app,
                $value
            );

            if ($board instanceof Board && $board->exists) {
                $board->applicationSingleton = true;

                // Binds the board to the application if it exists.
                $this->app->singleton(Board::class, function ($app) use ($board) {
                    return $board;
                });

                return $board;
            }

            return abort(404);
        });

        $router->bind('attachment', function ($value, $route) {
            return FileAttachment::where('is_deleted', false)
                ->with('storage')
                ->find($value);
        });

        $router->bind('role', function ($value, $route) {
            if (is_numeric($value)) {
                return Role::find($value);
            } elseif (preg_match('/^[a-z0-9]{1,64}\.(?P<id>\d+)$/i', $value, $matches)) {
                return Role::find($matches['id']);
            }
        });

        $router->bind('post', function ($value, $route) {
            $board = $route->getParameter('board');

            if (!($board instanceof Board)) {
                $board = $this->app->make(Board::class);
            }

            if (is_numeric($value) && $board instanceof Board) {
                $post = $board->getThreadByBoardId($value);

                if ($post instanceof Post && $post->exists) {
                    $route->setParameter('post', $post);

                    $this->app->singleton(Post::class, function ($app) use ($post) {
                        return $post;
                    });
                }
            }
        });

        // Sets up our routing tokens.
        $router->pattern('attachment', '[0-9]\d*');
        $router->pattern('board', Board::URI_PATTERN);
        $router->pattern('id', '[0-9]\d*');
        $router->pattern('splice', '(l)?(\d+)?(-)?(\d+)?');

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function map(Router $router)
    {
        /**
         * Media distribution
         */
        $router->group($this->routeGroup['media'], function ($router) {
            require app_path('Http/Routes/Media.php');
        });

        /**
         * Panel
         */
        if (config('app.url_panel', false)) {
            $this->routeGroup['panel'] += [ 'domain' => config('app.url_panel'), ];
        } else {
            $this->routeGroup['panel'] += [ 'prefix' => 'cp', ];
        }

        $router->group($this->routeGroup['panel'], function ($router) {
            require app_path('Http/Routes/Panel.php');
        });

        /**
         * Main web group
         */
        $router->group($this->routeGroup['web'], function ($router) {
            /**
             * Root-level content requests
             */
            $router->group($this->routeGroup['content'], function ($router) {
                require app_path('Http/Routes/Content.php');
            });

            /**
             * Boards
             */
            $router->group($this->routeGroup['board'], function ($router) {
                require app_path('Http/Routes/Board.php');
            });

            // This has to be dead last or the welcome controller starts
            // getting greedy.
            require app_path('Http/Routes/Web.php');
        });
    }
}
