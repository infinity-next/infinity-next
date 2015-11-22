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
	
	const VIEW_OVERBOARD = "overboard";
	
	public function getOverboard()
	{
		$threads = Post::getThreadsForOverboard(max(1, Input::get('page', 1)));
		
		return $this->view(static::VIEW_OVERBOARD, [
			'threads' => $threads,
		]);
	}
	
}
