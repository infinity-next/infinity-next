<?php namespace App\Http\Controllers\Panel;

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
	
	const VIEW_HISTORY = "multiboard";
	
	public function getHistory($ip = null)
	{
		if (!$this->user->canViewRawIP())
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
		]);
	}
	
}
