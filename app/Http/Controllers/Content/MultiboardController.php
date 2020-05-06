<?php

namespace App\Http\Controllers\Content;

use App\Board;
use App\Post;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Request;

/**
 * Renders a stream of content from all boards.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class MultiboardController extends Controller
{
    const VIEW_OVERBOARD = 'overboard';

    /**
     * Pull threads for the overboard.
     *
     * @static
     * @param  int  $page
     * @param  bool|null  $worksafe If we should only allow worksafe/nsfw.
     * @param  array  $include Boards to include.
     * @param  array  $exclude Boards to exclude.
     * @param  bool  $catalog Catalog view.
     * @param  integer  $updatedSince
     * @return Collection of static
     */

    protected function getThreads($page = 0, $worksafe = null, array $include = [], array $exclude = [], $catalog = false, $updatedSince = null)
    {
        $postsPerPage = $catalog ? 150 : 10;
        $boards = [];
        $threads = Post::whereHas('board', function ($query) use ($worksafe, $include, $exclude) {
            $query->where('is_indexed', true);
            $query->where('is_overboard', true);

            $query->where(function ($query) use ($worksafe, $include, $exclude) {
                $query->where(function ($query) use ($worksafe, $exclude) {
                    if (!is_null($worksafe)) {
                        $query->where('is_worksafe', $worksafe);
                    }
                    if (count($exclude)) {
                        $query->whereNotIn('boards.board_uri', $exclude);
                    }
                });

                if (count($include)) {
                    $query->orWhereIn('boards.board_uri', $include);
                }
            });
        })->thread();

        // Add replies
        $threads = $threads
            ->withEverythingAndReplies()
            ->with(['replies' => function ($query) use ($catalog) {
                if ($catalog) {
                    $query->where('body_has_content', true)->orderBy('post_id', 'desc')->limit(10);
                }
                else {
                    $query->forIndex();
                }
            }]);

        if ($updatedSince) {
            $threads = $threads->whereRaw("GREATEST(posts.bumped_last, posts.deleted_at) > '" . Carbon::createFromTimestamp($updatedSince) . "'");
        }

        if (Request::wantsJson()) {
            $threads = $threads->withTrashed();
        }

        $threads = $threads
            ->whereNull('suppressed_at')
            ->orderByRaw('LEAST(bumped_last, suppressed_at) DESC')
            ->skip($postsPerPage * ($page - 1))
            ->take($postsPerPage)
            ->get();

        // The way that replies are fetched forIndex pulls them in reverse order.
        // Fix that.
        foreach ($threads as $thread) {
            if (!isset($boards[$thread->board_uri])) {
                $boards[$thread->board_uri] = Board::getBoardWithEverything($thread->board_uri);
            }

            $thread->setRelation('board', $boards[$thread->board_uri]);

            $replyTake = $thread->stickied_at ? 1 : 5;

            $thread->body_parsed = $thread->getBodyFormatted();
            $thread->replies = $thread->replies
                ->sortBy('post_id')
                ->splice(-$replyTake, $replyTake);

            $thread->replies->each(function($reply) use ($boards) {
                $reply->setRelation('board', $boards[$reply->board_uri]);
            });

            $thread->prepareForCache();
        }

        return $threads;
    }

    protected function prepareThreads($worksafe = null, $boards = null, $catalog = false, $updatedSince = null)
    {
        $includes = [];
        $excludes  = [];

        if (!is_null($boards)) {
            // Break apart a board filter string.
            // Examples: +want -donotwant +want+alsowant-notthis
            $matchCount = preg_match_all(
                '/((?P<sign>\+|-)(?P<board_uri>[a-z0-9]{1,32}))/',
                $boards,
                $matches
            );

            for ($x = 0; $x < $matchCount; ++$x) {
                $sign = $matches['sign'][$x];
                $board_uri = $matches['board_uri'][$x];

                if ($sign === "-") {
                    $excludes[] = $board_uri;
                }
                else {
                    $includes[] = $board_uri;
                }
            }
        }

        return $this->getThreads(
            max(1, Request::input('page', 1)),
            is_null($worksafe) ? null : ($worksafe === "nsfw" ? false : true),
            $includes,
            $excludes,
            !!$catalog,
            $updatedSince
        );
    }

    public function getOverboardCatalogWithWorksafe($worksafe)
    {
        return $this->getOverboard($worksafe, null, true);
    }

    public function getOverboardCatalogWithBoards($boards)
    {
        return $this->getOverboard(null, $boards, true);
    }

    public function getOverboardCatalogAll()
    {
        return $this->getOverboard(null, null, true);
    }

    public function getOverboardCatalog($worksafe = null, $boards = null)
    {
        return $this->getOverboard($worksafe, $boards, true);
    }

    public function getOverboardWithWorksafe($worksafe)
    {
        return $this->getOverboard($worksafe, null);
    }

    public function getOverboardWithBoards($boards)
    {
        return $this->getOverboard(null, $boards);
    }

    public function getOverboard($worksafe = null, $boards = null, $catalog = false)
    {
        return $this->makeView(static::VIEW_OVERBOARD, [
            'worksafe' => $worksafe,
            'boards'   => $boards,
            'catalog'  => $catalog,
            'threads'  => $this->prepareThreads($worksafe, $boards, $catalog),
        ]);
    }
}
