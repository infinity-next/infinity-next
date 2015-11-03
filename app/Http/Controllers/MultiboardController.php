<?php namespace App\Http\Controllers;

use App\Post;
use Carbon\Carbon;

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
		$posts = Post::with('op', 'board')
			->paginate(15);
		
		return $this->view(static::VIEW_MULTIBOARD, [
			'posts' => $posts,
		]);
	}
	
}
