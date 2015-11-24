<?php namespace App\Http\Controllers\API\Board;

use App\Board;
use App\Post;

use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Board\BoardController as ParentController;
use App\Http\MessengerResponse;
use App\Http\Requests\PostRequest;

use Illuminate\Http\Request;
use Carbon\Carbon;

class BoardController extends ParentController implements ApiContract {
	
	use ApiController;
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @var Board $board
	 * @var integer $page
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
		
		return $this->apiResponse($posts);
	}
	
	/**
	 * Show the catalog (gridded) board view.
	 *
	 * @param  Board    $board
	 * @return Response
	 */
	public function getCatalog(Board $board)
	{
		// Load our list of threads and their latest replies.
		return $this->apiResponse($board->getThreadsForCatalog());
	}
	
	/**
	 * Returns a post to the client. 
	 *
	 * @param  Board    $board
	 * @param  Post     $thread
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
	 * @param  Request  $request
	 * @param  Board    $board
	 * @param  Post     $thread
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, Post $thread, $splice = null)
	{
		$input = $request->only('updatesOnly', 'updateHtml', 'updatedSince');
		
		if (isset($input['updatesOnly']))
		{
			$updatedSince = Carbon::createFromTimestamp($request->input('updatedSince', 0));
			$includeHTML  = isset($input['updateHtml']);
			
			$posts = Post::getUpdates($updatedSince, $board, $thread, $includeHTML);
			$posts->sortBy('board_id');
			
			return $this->apiResponse($posts);
		}
		
		return $this->apiResponse($thread);
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
		$response = parent::putThread($request, $board, $thread);
		
		if ($response instanceof Post)
		{
			$response = [
				'redirect' => $response->getURL(),
			];
		}
		
		return $this->apiResponse($response);
	}
}