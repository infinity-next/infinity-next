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
		
		return $this->viewReports();
	}
	
	public function dismiss(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		$report->is_dismissed = true;
		$report->is_successful = false;
		$report->save();
		
		return $this->viewReports();
	}
	
	
	public function dismissAll(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		Report::whereOpen()
			->whereResponsibleFor($this->user)
			->where('reporter_ip', $report->reporter_ip)
			->update([
				'is_dismissed'  => true,
				'is_successful' => false,
			]);
		
		return redirect()->back();
	}
	
	
	
	
	public function viewReports()
	{
		return $this->view(static::VIEW_REPORTS, [
			'reportedPosts' => $this->user->getReportedPostsViewable(),
		]);
	}
}