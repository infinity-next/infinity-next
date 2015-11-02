<?php namespace App\Http\Controllers;

use App\Post;
use App\Http\Controllers\Board\BoardStats;
use Illuminate\Support\Facades\View;

class WelcomeController extends Controller {
	
	use BoardStats;
	
	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/
	
	/**
	 * View file for the main index page container.
	 *
	 * @var string
	 */
	const VIEW_INDEX = "index";
	
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if ($featured = Post::getPostFeatured())
		{
			$featured->setRelation('replies', []);
		}
		
		return $this->view(static::VIEW_INDEX, [
			'featured' => $featured,
			'stats'    => $this->boardStats(),
		]);
	}
	
}
