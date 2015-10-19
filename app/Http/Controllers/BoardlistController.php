<?php namespace App\Http\Controllers;

use App\Board;

use App\Http\Controllers\Board\BoardStats;

use Illuminate\Pagination\LengthAwarePaginator;

use Input;
use Request;

class BoardlistController extends Controller {
	
	use BoardStats;
	
	/*
	|--------------------------------------------------------------------------
	| Boardlist Controller
	|--------------------------------------------------------------------------
	|
	|
	|
	*/
	
	/**
	 * View file for the main index page container.
	 *
	 * @var string
	 */
	const VIEW_INDEX = "boardlist";
	
	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if (Request::wantsJson())
		{
			return $this->boardListSearch()->toJson();
		}
		
		return $this->view(static::VIEW_INDEX, [
			'stats'  => $this->boardStats(),
			'boards' => $this->boardListSearch(),
		]);
	}
	
	protected function boardListInput()
	{
		return Input::only('sfw', 'title', 'lang', 'tags');;
	}
	
	protected function boardListSearch($perPage = 25)
	{
		$page = Request::get('page', 1);
		
		$boards = Board::select('board_uri', 'title', 'description', 'posts_total')
			->where(function($query) {
				
				// Are we requesting SFW only?
				if (Request::get('sfw', false))
				{
					$query->whereSFW();
				}
				
				// Are we able to view unindexed boards?
				if (!$this->user->canViewUnindexedBoards())
				{
					$query->whereIndexed(true);
				}
				
			})
			->with([
				'stats' => function($query) {
					$query->where('stats_time', '>=', \Carbon\Carbon::now()->minute(0)->second(0)->subDays(3));
				},
				'stats.uniques',
			])
			->get()
			->sortByDesc('stats_active_users');
		
		$paginator = new LengthAwarePaginator(
			$boards->forPage($page, $perPage),
			$boards->count(),
			$perPage,
			$page
		);
		
		
		$input = $this->boardListInput();
		
		foreach ($input as $inputIndex => $inputValue)
		{
			if ($inputIndex == "sfw")
			{
				$inputIndex = (int) !!$inputValue;
			}
			
			$paginator->appends($inputIndex, $inputValue);
		}
		
		
		return $paginator;
	}
}
