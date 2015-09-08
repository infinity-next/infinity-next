<?php namespace App\Http\Controllers\API\Board;

use App\Board;
use App\Post;

use App\Contracts\ApiController;
use App\Http\Controllers\PageController as ParentController;

use Illuminate\Http\Request;
use Carbon\Carbon;

class BoardController extends ParentController implements ApiController {
	
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
		
		return $posts;
	}
	
	/**
	 * Show the catalog (gridded) board view.
	 *
	 * @var Board $board
	 * @return Response
	 */
	public function getCatalog(Board $board)
	{
		// Load our list of threads and their latest replies.
		return $board->getThreadsForCatalog();
	}
	
	/**
	 * Returns a post to the client. 
	 *
	 * @var Board $board
	 * @var integer|null $post
	 * @return Response
	 */
	public function getPost(Board $board, $post)
	{
		if (is_null($post))
		{
			return abort(404);
		}
		
		// Pull the post.
		$post = $board->posts()
			->where('board_id', $post)
			->withEverything()
			->firstOrFail();
		
		if (!$post)
		{
			return abort(404);
		}
		
		return $post;
	}
	
	/**
	 * Returns a thread and its replies to the client. 
	 *
	 * @var Board $board
	 * @var integer|null $thread
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, $thread)
	{
		if (is_null($thread))
		{
			return abort(404);
		}
		
		$input = $request->only('updatesOnly', 'updateHtml', 'updatedSince');
		
		if (isset($input['updatesOnly']))
		{
			$updatedSince = Carbon::createFromTimestamp($request->input('updatedSince', 0));
			
			$posts = Post::where('posts.board_uri', $board->board_uri)
				->withEverything()
				->where(function($query) use ($thread) {
					$query->where('posts.reply_to_board_id', $thread);
					$query->orWhere('posts.board_id', $thread);
				})
				->where(function($query) use ($updatedSince) {
					$query->where('posts.updated_at', '>=', $updatedSince);
					$query->orWhere('posts.deleted_at', '>=', $updatedSince);
				})
				->withTrashed()
				->orderBy('posts.created_at', 'asc')
				->get();
			
			if (isset($input['updateHtml']))
			{
				foreach ($posts as $post)
				{
					$appends   = $post->getAppends();
					$appends[] = "html";
					$post->setAppends($appends);
				}
			}
			
			return $posts;
		}
		else
		{
			// Pull the thread.
			$thread = $board->getThreadByBoardId($thread);
			
			if (!$thread)
			{
				return abort(404);
			}
		}
		
		return $thread;
	}
	
}