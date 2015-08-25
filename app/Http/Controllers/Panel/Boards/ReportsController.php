<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Report;
use App\Http\Controllers\Panel\PanelController;

class ReportsController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Reprts Controller
	|--------------------------------------------------------------------------
	|
	| The reports controller will display and allow the handling of posts reports,
	| either on all boards (for global moderators), or local boards.
	|
	*/
	
	const VIEW_REPORTS = "panel.board.reports";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	public function getIndex()
	{
		if (!$this->user->canViewReports())
		{
			abort(403);
		}
		
		$reports = $this->user->getReportedPostsViewable();
		
		return $this->view(static::VIEW_REPORTS, [
			'reportedPosts' => $reports,
		]);
	}
}