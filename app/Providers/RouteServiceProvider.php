<?php

namespace App\Providers;

use App\Ban;
use App\Board;
use App\FileAttachment;
use App\Page;
use App\Post;
use App\Report;
use App\Role;
use App\User;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Complex HTTP/S routing provider.
 *
 * @category   Provider
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
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

        'api' => [
            'as'         => 'api.',
            'middleware' => ['api'],
            'namespace'  => 'App\Http\Controllers\API',
        ],

        'esi'     => 'ESI',
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->model('attachment', FileAttachment::class);
        $router->model('ban',        Ban::class);
        $router->model('board',      Board::class);
        $router->model('page',       Page::class);
        $router->model('post',       Post::class);
        $router->model('user',       User::class);
        $router->model('report',     Report::class);
        $router->model('role',       Role::class);

        $router->bind('page_title', function ($value, $route) {
            $board = $route->getParameter('board');

            return Page::where([
                'board_uri' => $board instanceof Board ? $board->board_uri : null,
                'title' => $value,
            ])->first();
        });

        $router->bind('board', function ($value, $route) {
            $board = Board::getBoardWithEverything($value);

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

        $router->bind('post_id', function ($value, $route) {
            $board = $route->getParameter('board');

            if (!($board instanceof Board)) {
                $board = $this->app->make(Board::class);
            }
            if (is_numeric($value) && $board instanceof Board) {
                $post = $board->getThreadByBoardId($value);

                if ($post instanceof Post && $post->exists) {
                    $route->setParameter('post', $post);
                    $route->setParameter('post_id', $post);

                    $this->app->singleton(Post::class, function ($app) use ($post) {
                        return $post;
                    });

                    return $post;
                }
            }
        });

        $router->bind('post', function ($value, $route) {
            $board = $route->getParameter('board');

            if (!($board instanceof Board)) {
                $board = $this->app->make(Board::class);
            }
            if (is_numeric($value) && $board instanceof Board) {
                $post = Post::find($value);

                if ($post instanceof Post && $post->exists) {
                    $route->setParameter('post', $post);

                    $this->app->singleton(Post::class, function ($app) use ($post) {
                        return $post;
                    });

                    return $post;
                }
            }
        });

        // Sets up our routing tokens.
        $router->pattern('attachment', '[0-9]\d*');
        $router->pattern('board', Board::URI_PATTERN);
        $router->pattern('id', '[0-9]\d*');
        $router->pattern('splice', '(l)?(\d+)?(-)?(\d+)?');

        $router->pattern('worksafe', '^(sfw|nsfw)$');
        $router->pattern('boards', '^((\+|-)[a-z0-9]{1,32})+$');

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
            require __DIR__.'/../Http/Routes/Media.php';
        });

        /**
         * API
         */
        $router->group($this->routeGroup['api'], function ($router) {
            require __DIR__.'/../Http/Routes/API.php';
        });

        /**
         * Panel
         */
        if (config('app.url_panel', false)) {
            $this->routeGroup['panel'] += ['domain' => config('app.url_panel'),];
        } else {
            $this->routeGroup['panel'] += ['prefix' => 'cp',];
        }

        $router->group($this->routeGroup['panel'], function ($router) {
            require __DIR__.'/../Http/Routes/Panel.php';
        });

        /**
         * Main web group
         */
        if (config('app.url', false)) {
            $this->routeGroup['web'] += ['domain' => config('app.url'),];
        }

        $router->group($this->routeGroup['web'], function ($router) {
            /**
             * Root-level content requests
             */
            $router->group($this->routeGroup['content'], function ($router) {
                require __DIR__.'/../Http/Routes/Content.php';
            });

            /**
             * Boards
             */
            $router->group($this->routeGroup['board'], function ($router) {
                require __DIR__.'/../Http/Routes/Board.php';
            });

            // This has to be dead last or the welcome controller starts
            // getting greedy.
            require __DIR__.'/../Http/Routes/Web.php';
        });
    }
}
