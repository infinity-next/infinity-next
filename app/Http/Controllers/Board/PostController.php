<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;
use App\Http\Controllers\MainController;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Request;
use View;

class PostController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| Post Controller
	|--------------------------------------------------------------------------
	|
	| Any request to be acted upon a single, extant post is carried out through
	| this form.
	| 
	| Note that creating a NEW post against a board is handled
	| in the BoardController instead.
	| 
	*/
	
	/**
	 * 
	 */
	public function getMod(Request $request, Board $board, $post)
	{
		// Validate the request parameters.
		if(!(($post = $this->validatePost($board, $post)) instanceof Post))
		{
			// If the response isn't a Post, it's a redirect or error.
			// Return the message.
			return $post;
		}
		
		// Take trailing arguments,
		// compare them against a list of real actions,
		// intersect the liss to find the true commands.
		$actions    = ["delete", "ban", "all", "global"];
		$argList    = func_get_args();
		$modActions = array_intersect($actions, array_splice($argList, 2));
		
		$ban        = in_array("ban",    $modActions);
		$delete     = in_array("delete", $modActions);
		$all        = in_array("all",    $modActions);
		$global     = in_array("global", $modActions);
		
		if (!$ban && !$delete)
		{
			return abort(404);
		}
		
		if ($ban)
		{
			return "This is where the ban page would go. IF I HAD ONE.";
		}
		else if ($delete)
		{
			if ($global)
			{
				if (!$this->user->canDeleteGlobally())
				{
					return abort(403);
				}
				
				
				Post::where('author_ip', $post->author_ip)
					->delete();
				
				return redirect($board->board_uri);
			}
			else
			{
				if (!$board->canDelete($this->user))
				{
					return abort(403);
				}
				
				if ($all)
				{
					Post::where('author_ip', $post->author_ip)
						->where('board_uri', $board->board_uri)
						->delete();
					
					return redirect($board->board_uri);
				}
				else
				{
					$post->delete();
					
					if ($post->reply_to)
					{
						return redirect("{$board->board_uri}/thread/{$post->op->board_id}");
					}
					else
					{
						return redirect($board->board_uri);
					}
				}
			}
		}
		
		
		return abort(403);
	}
	
	/**
	 * 
	 */
	public function getEdit(Request $request, Board $board, $post)
	{
		// Validate the request parameters.
		if(!(($post = $this->validatePost($board, $post)) instanceof Post))
		{
			// If the response isn't a Post, it's a redirect or error.
			// Return the message.
			return $post;
		}
		
		if ($post->canEdit($this->user))
		{
			return View::make('content.', [
				'board'    => $board,
				'post'     => $post,
			]);
		}
		
		return abort(403);
	}
	
	
	/**
	 * Stickies a thread.
	 */
	public function anySticky(Request $request, Board $board, $post, $sticky = true)
	{
		// Validate the request parameters.
		if(!(($post = $this->validatePost($board, $post)) instanceof Post))
		{
			// If the response isn't a Post, it's a redirect or error.
			// Return the message.
			return $post;
		}
		
		if ($post->canSticky($this->user))
		{
			$post->setSticky( $sticky )->save();
			return redirect("{$board->board_uri}/thread/{$post->board_id}");
		}
		
		return abort(403);
	}
	
	/**
	 * Unstickies a thread.
	 */
	public function anyUnsticky(Request $request, Board $board, $post)
	{
		// Redirect to anySticky with a flag denoting an unsticky.
		return $this->anySticky($request, $board, $post, false);
	}
	
	
	/**
	 * Check the request for all post controller methods.
	 *
	 * @return HttpException|RedirectResponse|\App\Post
	 */
	protected function validatePost(Board $board, $post)
	{
		// If no post is specified, we can't do anything.
		// Push the user to the index.
		if (is_null($post))
		{
			return redirect($board->board_uri);
		}
		
		// Find the post.
		$post = $board->getLocalReply($post);
		
		if (!$post)
		{
			return abort(404);
		}
		
		return $post;
	}
}
