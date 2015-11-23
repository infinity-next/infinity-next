<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;
use App\FileStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Validators\FileValidator;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

use Input;
use File;
use Response;
use Validator;

class BoardController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Board Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles any requests that point to a directory which is
	| otherwise unavailable. It will determine if such a board exists and then
	| distribute content based on what what additional directries are specified
	| and what information is available to the accessing user.
	|
	*/
	
	const VIEW_BOARD   = "board";
	const VIEW_CATALOG = "catalog";
	const VIEW_THREAD  = "board";
	const VIEW_LOGS    = "board.logs";
	
	public function boot()
	{
		Validator::resolver(function($translator, $data, $rules, $messages)
		{
			return new FileValidator($translator, $data, $rules, $messages);
		});
	}
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @param  Board  $board
	 * @param  integer  $page
	 * @return Response
	 */
	public function getIndex(Board $board, $page = 1)
	{
		// Determine what page we are on.
		$pages = $board->getPageCount();
		
		// Clamp the page to real values.
		if ($page <= 0)
		{
			$page = 1;
		}
		elseif ($page > $pages)
		{
			$page = $pages;
		}
		
		// Determine if we have a next/prev button.
		$pagePrev = ($page > 1) ? $page - 1 : false;
		$pageNext = ($page < $pages) ? $page + 1 : false;
		
		// Load our list of threads and their latest replies.
		$posts = $board->getThreadsForIndex($page);
		
		return $this->view(static::VIEW_BOARD, [
			'board'    => &$board,
			'posts'    => $posts,
			'reply_to' => false,
			
			'pages'    => $pages,
			'page'     => $page,
			'pagePrev' => $pagePrev,
			'pageNext' => $pageNext,
		] );
	}
	
	/**
	 * Show the catalog (gridded) board view.
	 *
	 * @param  Board  $board
	 * @return Response
	 */
	public function getCatalog(Board $board)
	{
		// Load our list of threads and their latest replies.
		$posts = $board->getThreadsForCatalog();
		
		return $this->view(static::VIEW_CATALOG, [
			'board'    => $board,
			'posts'    => $posts,
			'reply_to' => false,
		] );
	}
	
	/**
	 * Renders a thread.
	 *
	 * @var Board $board
	 * @return Response
	 */
	public function getLogs(Board $board)
	{
		return $this->view(static::VIEW_LOGS, [
			'board' => $board,
			'logs'  => $board->getLogs(),
		]);
	}
	
	/**
	 * Renders a thread.
	 *
	 * @param  Board  $board
	 * @param  Post   $thread
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, Post $thread, $splice = null)
	{
		if (!$thread->exists)
		{
			return abort(404);
		}
		else if ($thread->reply_to)
		{
			return redirect("{$board->board_uri}/thread/{$thread->reply_to_board_id}{$sign}{$count}#{$thread->board_id}");
		}
		
		$thread = $thread->forThreadView(is_string($splice) ? $splice : null);
		
		if ($thread === false)
		{
			abort(400);
		}
		
		return $this->view(static::VIEW_THREAD, [
			'board'    => &$board,
			'posts'    => [ $thread ],
			'reply_to' => $thread,
		]);
	}
	
	/**
	 * Handles the creation of a new thread or reply.
	 *
	 * @param  \App\Http\Requests\PostRequest  $request
	 * @param  Board  $board
	 * @param  Post|null  $thread
	 * @return Response (redirects to the thread view)
	 */
	public function putThread(PostRequest $request, Board $board, Post $thread = null)
	{
		// Create the post.
		$post = new Post($request->all());
		$post->submitTo($board, $thread);
		
		
		// Log staff posts.
		if ($post->capcode_id)
		{
			$this->log('log.post.capcode', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
				"capcode"   => $post->capcode->getCapcodeName(),
				"role"      => $post->capcode->role,
			]);
		}
		
		$input = $request->only('updatesOnly', 'updateHtml', 'updatedSince');
		
		if ($request->wantsJson())
		{
			if (!is_null($thread) && $thread->exists)
			{
				$updatedSince = Carbon::createFromTimestamp($request->input('updatedSince', Carbon::now()->timestamp));
				$includeHTML  = isset($input['updateHtml']);
				
				$posts = Post::getUpdates($updatedSince, $board, $thread, $includeHTML);
				$post->setAppendHTML($includeHTML);
				$posts->push($post);
				$posts->sortBy('board_id');
				
				return $posts;
			}
			else
			{
				return $post;
			}
		}
		
		// Redirect to the new post or thread.
		if ($post->reply_to_board_id)
		{
			return redirect("{$board->board_uri}/thread/{$post->reply_to_board_id}#{$post->board_id}");
		}
		else
		{
			return redirect("{$board->board_uri}/thread/{$post->board_id}#{$post->board_id}");
		}
	}
	
	/**
	 * Supplies a user generated stylesheet based on board options.
	 *
	 * @param  Board  $board
	 * @return Response
	 */
	public function getStylesheet(Board $board)
	{
		if (!$board->hasStylesheet())
		{
			abort(404);
		}
		
		$stylesheet  = $board->getStylesheet();
		$statusCode  = 200;
		$contentType = "text/css";
		
		if (strlen((string) $stylesheet) == 0)
		{
			return abort(404);
		}
		
		return response($stylesheet, $statusCode)
			->header('Content-Type', $contentType);
	}
	
	/**
	 * Supplies a user generated stylesheet based on board options in plaintext as to avoid compression.
	 *
	 * @param  Board  $board
	 * @return Response
	 */
	public function getStylesheetAsText(Board $board)
	{
		return $this->getStylesheet($board)
			->header('Content-Type', "text/plain");
	}
	
	/**
	 * Checks if a file exists.
	 *
	 * @param  Request  $request
	 * @param  Board  $board
	 * @return json
	 */
	public function getFile(Request $request, Board $board)
	{
		$hash    = $request->get('md5');
		$storage = FileStorage::getHash($hash);
		
		if (is_null($storage) || !$storage->hasFile())
		{
			return [ $hash => null ];
		}
		
		return [ $hash => $storage ];
	}
	
	/**
	 * Uploads a single file.
	 *
	 * @param  Request  $request
	 * @param  Board  $board
	 * @return json
	 */
	public function putFile(Request $request, Board $board)
	{
		$input = Input::all();
		$rules = [];
		
		PostRequest::rulesForFiles($board, $rules);
		$rules['files'][] = "required";
		
		$validator = Validator::make($input, $rules);
		
		if (!$validator->passes())
		{
			return json_encode([
				'errors' => $validator->errors(),
			]);
		}
		
		
		$storage = new Collection;
		
		foreach ($input['files'] as $file)
		{
			$newStorage = FileStorage::storeUpload($file);
			$storage[$newStorage->hash] = $newStorage;
		}
		
		return $storage;
	}
}
