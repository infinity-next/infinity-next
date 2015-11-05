<?php namespace App\Http\Controllers;

use App\Post;
use Carbon\Carbon;

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
			return Post::with('op', 'board', 'board.assets')
				->withEverything()
				->orderBy('post_id', 'desc')
				->take(75)
				->paginate(15);
		});
		
		return $this->view(static::VIEW_MULTIBOARD, [
			'posts' => $posts,
		]);
	}
	
}
