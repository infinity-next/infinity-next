<?php namespace App\Http\Controllers\Panel\Boards;

use App\BanAppeal;
use App\Board;
use App\Post;
use App\Report;
use App\Http\Controllers\Panel\PanelController;

class AppealsController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Appeals Controller
	|--------------------------------------------------------------------------
	|
	| Lists applicable appeals and dispatches actions on them.
	|
	*/

	const VIEW_APPEALS = "panel.board.appeals";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var str
	 */
	public static $navSecondary = "nav.panel.home";
	
	public function getIndex(Board $board = null)
	{
		if (!$this->user->canManageAppealsAny())
		{
			return abort(403);
		}
		
		$appeals = BanAppeal::getAppealsFor($this->user);
		
		return $this->view(static::VIEW_APPEALS, [
			'appeals' => $appeals,
		]);
	}
	
}
