<?php namespace App\Http\Controllers\Panel;

use App\Board;
use App\Post;
use App\Support\IP;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

use Cache;
use Input;
use Request;
use Validator;

class HistoryController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| History Controller
	|--------------------------------------------------------------------------
	|
	| Pulls history for an address.
	|
	*/
	
	const VIEW_HISTORY = "history";
	
	public function getHistory($ip = null)
	{
		if (!$this->user->canViewGlobalHistory())
		{
			return abort(403);
		}
		
		if (is_null($ip))
		{
			return abort(404);
		}
		
		try
		{
			$ip = new IP($ip);
		}
		catch (\InvalidArgumentException $e)
		{
			return abort(404);
		}
		catch (\Exception $e)
		{
			throw $e;
		}
		
		$posts = Post::with('op', 'board', 'board.assets')
			->withEverything()
			->where('author_ip', $ip)
			->orderBy('post_id', 'desc')
			->paginate(15);
		
		return $this->view(static::VIEW_HISTORY, [
			'posts' => $posts,
			'ip'    => ip_less($ip->toText()),
		]);
	}
	
	public function getBoardHistory(Board $board, Post $post)
	{
		$posts = $board->posts()
			->with('op')
			->withEverything()
			->where('author_ip', $post->author_ip)
			->orderBy('post_id', 'desc')
			->paginate(15);
		
		foreach ($posts as $item)
		{
			$item->setRelation('board', $board);
		}
		
		return $this->view(static::VIEW_HISTORY, [
			'posts'      => $posts,
			'multiboard' => false,
			'ip'         => ip_less($ip->toText()),
		]);
	}
	
}
