<?php namespace App\Http\Controllers;

use App\Post;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

use Cache;
use Input;
use Request;
use Validator;

class MultiboardController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Multiboard Controller
	|--------------------------------------------------------------------------
	|
	| Renders a stream of content from all boards.
	|
	*/
	
	const VIEW_MULTIBOARD = "multiboard";
	
	public function getIndex()
	{
		$posts = Cache::remember('site.overboard', 60, function()
		{
			$posts = Post::with('op', 'board', 'board.assets')
				->withEverything()
				->orderBy('post_id', 'desc')
				->take(75)
				->get();
			
			return $posts;
		});
		
		$page    = max(1, Input::get('page', 1));
		$perPage = 15;
		
		$paginator = new LengthAwarePaginator(
			$posts->forPage($page, $perPage),
			$posts->count(),
			$perPage,
			$page
		);
		$paginator->setPath(url("overboard.html"));
		
		
		return $this->view(static::VIEW_MULTIBOARD, [
			'posts' => $paginator,
		]);
	}
	
}
