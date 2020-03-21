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
use Illuminate\Support\Facades\Route;
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
     */
    public function boot()
    {
        Route::model('attachment', FileAttachment::class);
        Route::model('ban',        Ban::class);
        Route::model('board',      Board::class);
        Route::model('page',       Page::class);
        Route::model('post',       Post::class);
        Route::model('user',       User::class);
        Route::model('report',     Report::class);
        Route::model('role',       Role::class);

        Route::bind('page_title', function ($value, $route) {
            $board = $route->getParameter('board');

            return Page::where([
                'board_uri' => $board instanceof Board ? $board->board_uri : null,
                'title' => $value,
            ])->first();
        });

        Route::bind('board', function ($value, $route) {
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

        Route::bind('attachment', function ($value, $route) {
            return FileAttachment::where('is_deleted', false)
                ->with('storage')
                ->find($value);
        });

        Route::bind('post_id', function ($value, $route) {
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

        Route::bind('post', function ($value, $route) {
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
        Route::pattern('attachment', '[0-9]\d*');
        Route::pattern('board', Board::URI_PATTERN);
        Route::pattern('id', '[0-9]\d*');
        Route::pattern('splice', '(l)?(\d+)?(-)?(\d+)?');

        Route::pattern('worksafe', '^(sfw|nsfw)$');
        Route::pattern('boards', '^((\+|-)[a-z0-9]{1,32})+$');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map()
    {
        /**
         * Media distribution
         */
        Route::group($this->routeGroup['media'], function ($router) {
            require __DIR__.'/../Http/Routes/Media.php';
        });

        /**
         * API
         */
        Route::group($this->routeGroup['api'], function ($router) {
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

        Route::group($this->routeGroup['panel'], function ($router) {
            require __DIR__.'/../Http/Routes/Panel.php';
        });

        /**
         * Main web group
         */
        if (config('app.url', false)) {
            $this->routeGroup['web'] += ['domain' => config('app.url'),];
        }

        Route::group($this->routeGroup['web'], function ($router) {
            /**
             * Root-level content requests
             */
            Route::group($this->routeGroup['content'], function ($router) {
                require __DIR__.'/../Http/Routes/Content.php';
            });

            /**
             * Boards
             */
            Route::group($this->routeGroup['board'], function ($router) {
                require __DIR__.'/../Http/Routes/Board.php';
            });

            // This has to be dead last or the welcome controller starts
            // getting greedy.
            require __DIR__.'/../Http/Routes/Web.php';
        });
    }
}
