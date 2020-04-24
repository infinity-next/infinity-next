<?php

namespace App\Http\Controllers\Board;

use App\Ban;
use App\Board;
use App\FileStorage;
use App\OptionGroup;
use App\Post;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Filesystem\Upload;
use App\Support\IP;
use App\Exceptions\BannedException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Cache;
use File;
use Request;
use Response;
use Validator;

/**
 * Controller for board related views.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class BoardController extends Controller
{
    const VIEW_BOARD = 'board';
    const VIEW_CATALOG = 'catalog';
    const VIEW_CONFIG = 'board.config';
    const VIEW_LANDING = 'board.landing';
    const VIEW_THREAD = 'board';
    const VIEW_LOGS = 'board.logs';

    /**
     * Show the board index for the user.
     * This is usually the last few threads, depending on the optional page
     * parameter, which determines the thread offset.
     *
     * @param \App\Board $board
     * @param int        $page
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

        return $this->makeView(static::VIEW_BOARD, [
            'board' => &$board,
            'posts' => $posts,
            'reply_to' => false,

            'pages' => $pages,
            'page' => $page,
            'pagePrev' => $pagePrev,
            'pageNext' => $pageNext,
        ]);
    }

    /**
     * Show the catalog (gridded) board view.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function getCatalog(Board $board)
    {
        // Load our list of threads and their latest replies.
        $posts = $board->getThreadsForCatalog();

        return $this->makeView(static::VIEW_CATALOG, [
            'board' => $board,
            'posts' => $posts,
            'reply_to' => false,
        ]);
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
        $optionGroups = OptionGroup::getBoardConfig($board);

        return $this->makeView(static::VIEW_CONFIG, [
            'board' => $board,
            'groups' => $optionGroups,
        ]);
    }

    /**
     * Renders a thread.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function getLogs(Board $board)
    {
        $this->authorize('audit', $board);

        return $this->makeView(static::VIEW_LOGS, [
            'board' => $board,
            'logs' => $board->getLogs(),
        ]);
    }

    /**
     * Renders a thread.
     *
     * @param Board  $board
     * @param Post   $thread
     * @param string $splice  2ch-style URL API
     *
     * @return Response
     */
    public function getThread(Board $board, Post $thread, $splice = null)
    {
        if (!$thread->exists) {
            return abort(404);
        }
        elseif ($thread->reply_to) {
            return redirect("{$board->board_uri}/thread/{$thread->reply_to_board_id}#{$thread->board_id}");
        }

        if (is_string($splice)) {
            $thread = $thread->getReplySplice($splice);
        }

        if ($thread === false) {
            abort(400);
        }

        return $this->makeView(static::VIEW_THREAD, [
            'board' => &$board,
            'posts' => [$thread],
            'reply_to' => $thread,
            'updater' => true,
        ]);
    }

    /**
     * Handles a redirect landing page.
     *
     * @param Board $board
     * @param Post  $thread
     *
     * @return Response
     */
    public function getThreadRedirect(Board $board, Post $thread)
    {
        return $this->makeView(static::VIEW_LANDING, [
            'board' => $board,
            'url' => $thread->getURL(),
            'message' => trans($thread->reply_to_board_id ? 'board.landing.reply_submitted' : 'board.landing.thread_submitted'),
        ]);
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
        $captcha = $request->input('captcha_hash', null);
        if (!is_null($captcha) && !Cache::lock("captcha:{$captcha}", 5, Post::class)->get()) {
            return abort(429, "This captcha is already being used.");
        }

        try {
            $request->validate();
        }
        catch (BannedException $e) {
            if ($request->wantsJson()) {
                return [ 'redirect' => $e->redirectTo ];
            }
            else {
                return redirect($e->redirectTo);
            }
        }

        // Create the post.
        $post = new Post($request->all());
        $post->board()->associate($board);
        $post->thread()->associate($thread);
        $post->save();

        // $input = $request->only('updatesOnly', 'updateHtml', 'updatedSince');

        if ($request->wantsJson()) {
            /*if (!is_null($thread) && $thread->exists) {
                $updatedSince = Carbon::createFromTimestamp($request->input('updatedSince', Carbon::now()->timestamp));
                $includeHTML = isset($input['updateHtml']);

                $post->setAppendHTML($includeHTML);

                $posts = Post::getUpdates($updatedSince, $board, $thread, $includeHTML);
                $posts->push($post);
                $posts->sortBy('board_id');

                return $posts;
            }*/

            return [ $post ];
        }

        // Redirect to splash page.
        return redirect("/{$board->board_uri}/redirect/{$post->board_id}");
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
        $captcha = $request->input('captcha_hash', null);
        if (!is_null($captcha) && !Cache::lock("captcha:{$captcha}", 5, Post::class)->get()) {
            return abort(429, "This captcha is already being used.");
        }

        try {
            $request->validate();
        }
        catch (BannedException $e) {
            if ($request->wantsJson()) {
                return [ 'redirect' => $e->redirectTo ];
            }
            else {
                return redirect($e->redirectTo);
            }
        }

        // Create the post.
        $post = new Post($request->all());
        $post->board()->associate($board);
        $post->save();

        if ($request->wantsJson()) {
            return [ $post ];
        }

        // Redirect to splash page.
        return redirect("/{$board->board_uri}/redirect/{$post->board_id}");
    }

    /**
     * Supplies a user generated stylesheet based on board options.
     *
     * @param Board $board
     *
     * @return Response
     */
    public function getStylesheet(Board $board)
    {
        if (!$board->hasStylesheet()) {
            abort(404);
        }

        $stylesheet = $board->getStylesheet();
        $statusCode = 200;
        $contentType = 'text/css';
        $cacheTime = 86400; // 1 day

        if (strlen((string) $stylesheet) == 0) {
            return abort(404);
        }

        return response($stylesheet, $statusCode)
            ->header('Content-Type', $contentType)
            ->header('Cache-Control', "public, max-age={$cacheTime}, pre-check={$cacheTime}");
    }

    /**
     * Supplies a user generated stylesheet based on board options in plaintext as to avoid compression.
     *
     * @param Board $board
     *
     * @return Response
     */
    public function getStylesheetAsText(Board $board)
    {
        return $this->getStylesheet($board)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Checks if a file exists.
     *
     * @param Board   $board
     *
     * @return json
     */
    public function getFile(Board $board)
    {
        $hash = Request::input('sha256');
        $storage = FileStorage::getHash($hash);

        if (is_null($storage) || !$storage->hasFile()) {
            return response()->json([$hash => null]);
        }

        return response()->json([$hash => $storage]);
    }

    /**
     * Uploads a single file.
     *
     * @param Board   $board
     *
     * @return json
     */
    public function putFile(Board $board)
    {
        $this->authorize('create-attachment');

        $input = Request::all();
        $rules = [];

        PostRequest::rulesForFiles($board, $rules);
        $rules['files'][] = 'required';

        $validator = Validator::make($input, $rules);

        if (!$validator->passes()) {
            return response()->json(['errors' => $validator->errors(),], 422);
        }

        $storage = collect([]);

        foreach ($input['files'] as $file) {
            $upload = new Upload($file);
            $fileStorage = $upload->process();

            $storage->push($fileStorage);
        }

        return $storage;
    }
}
