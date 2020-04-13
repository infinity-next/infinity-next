<?php

namespace App\Http\Controllers\API\Board;

use App\Board;
use App\Post;
use App\Option;
use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Board\BoardController as ParentController;
use App\Http\Requests\PostRequest;
use Carbon\Carbon;
use Request;

/**
 * Controller for board related API.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class BoardController extends ParentController implements ApiContract
{
    use ApiController;

    /**
     * Show the board index for the user.
     * This is usually the last few threads, depending on the optional page
     * parameter, which determines the thread offset.
     *
     * @var Board
     * @var int   $page
     *
     * @return Response
     */
    public function getIndex(Board $board, $page = 1)
    {
        // Determine what page we are on.
        $pages = $board->getPageCount();

        // Clamp the page to real values.
        if ($page <= 0) {
            $page = 1;
        } elseif ($page > $pages) {
            $page = $pages;
        }

        // Determine if we have a next/prev button.
        $pagePrev = ($page > 1) ? $page - 1 : false;
        $pageNext = ($page < $pages) ? $page + 1 : false;

        // Load our list of threads and their latest replies.
        $posts = $board->getThreadsForIndex($page);

        return $this->apiResponse($posts);
    }

    /**
     * Show the catalog (gridded) board view.
     *
     * @param Board $board
     *
     * @return Response
     */
    public function getCatalog(Board $board)
    {
        // Load our list of threads and their latest replies.
        return $this->apiResponse($board->getThreadsForCatalog());
    }

    /**
     * Renders public config.
     *
     * @param \App\Board $board
     *
     * @return REsponse
     */
    public function getConfig(Board $board)
    {
        return Option::andBoardSettings($board)->get();
    }

    /**
     * Returns a post to the client.
     *
     * @param Board $board
     * @param Post  $thread
     *
     * @return Response
     */
    public function getPost(Board $board, Post $post)
    {
        $post->setAppendHTML(true);
        $post->setRelation('replies', null);

        return $this->apiResponse($post);
    }

    /**
     * Returns a thread and its replies to the client.
     *
     * @param Board   $board
     * @param Post    $thread
     *
     * @return Response
     */
    public function getThread(Board $board, Post $thread, $splice = null)
    {
        $input = Request::only('updatesOnly', 'updateHtml', 'updatedSince');

        if (isset($input['updatesOnly'])) {
            $updatedSince = Carbon::createFromTimestamp(Request::input('updatedSince', 0));
            $includeHTML = isset($input['updateHtml']);

            $posts = Post::getUpdates($updatedSince, $board, $thread, $includeHTML);
            $posts->sortBy('board_id');

            return $this->apiResponse($posts);
        }

        return $this->apiResponse($thread);
    }

    /**
     * Handles the creation of a new thread or reply.
     *
     * @param \App\Http\Requests\PostRequest $request
     * @param Board                          $board
     * @param Post                           $thread
     * @return Response (redirects to the thread view)
     */
    public function putReply(PostRequest $request, Board $board, Post $thread)
    {
        $response = parent::putReply($request, $board, $thread);

        if ($response instanceof Post) {
            $response = [
                'post'     => $response->toArray(),
                'redirect' => $response->getUrl(),
            ];
        }
        //else {
        //    $response = $response
        //        ->sortByDesc('recently_created')
        //        ->unique(function ($post) {
        //            return $post->post_id;
        //        })
        //        ->sortBy('post_id');
        //
        //    $response = array_values($response->toArray());
        //}

        return $this->apiResponse($response);
    }

    /**
     * Handles the creation of a new thread or reply.
     *
     * @param \App\Http\Requests\PostRequest $request
     * @param Board                          $board
     * @return Response (redirects to the thread view)
     */
    public function putThread(PostRequest $request, Board $board)
    {
        $response = parent::putThread($request, $board);

        if ($response instanceof Post) {
            $response = [
                'post'     => $response->toArray(),
                'redirect' => $response->getUrl(),
            ];
        }
        //else {
        //    $response = $response
        //        ->sortByDesc('recently_created')
        //        ->unique(function ($post) {
        //            return $post->post_id;
        //        })
        //        ->sortBy('post_id');
        //
        //    $response = array_values($response->toArray());
        //}

        return $this->apiResponse($response);
    }
}
