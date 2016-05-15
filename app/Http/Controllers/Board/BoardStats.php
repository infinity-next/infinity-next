<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;
use Illuminate\Http\Request;
use Cache;

trait BoardStats {

	/*
	|--------------------------------------------------------------------------
	| Board Stats
	|--------------------------------------------------------------------------
	|
	| The BoardStats trait is used as a provider for returning global or local
	| information regrding posts and activities made around the site.
	|
	*/

	/**
	 * What information we're after.
	 *
	 * @var array
	 */
	protected $boardStats = [
			'boardIndexedCount',
			'boardTotalCount',
			'postCount',
			'postRecentCount',
			'startDate',
		];

	/**
	 * Returns the information specified by boardStats
	 *
	 * @return array (indexes and vales based on $boardStats)
	 */
	protected function boardStats()
	{
		$controller = &$this;

		$stats = Cache::remember('index.boardstats', 60, function() use ($controller)
		{
			$stats = [];

			foreach ($controller->boardStats as $boardStat)
			{
				switch ($boardStat)
				{
					case "boardIndexedCount" :
						$stats[$boardStat] = Board::whereIndexed()->count();
						break;

					case "startDate" :
						$stats[$boardStat] = Board::orderBy('created_at', 'desc')->take(1)->pluck('created_at');
						break;

					case "boardTotalCount" :
						$stats[$boardStat] = Board::count();
						break;

					case "postCount" :
						$stats[$boardStat] = Board::sum('posts_total');
						break;

					case "postRecentCount" :
						$stats[$boardStat] = Post::recent()->count();
						break;
				}
			}

			return $stats;
		});

		return $stats;
	}
}
