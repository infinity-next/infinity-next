<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Post;
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
	
	/**
	 * Displays a full list of reports.
	 * Handles /reports/
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		if (!$this->user->canViewReports())
		{
			abort(403);
		}
		
		return $this->viewReports();
	}
	
	
	/**
	 * Dismisses a single report and returns the user back to the reports page.
	 * Handles /report/{report}/dismiss
	 *
	 * @return Response
	 */
	public function getDismiss(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		$report->is_dismissed = true;
		$report->is_successful = false;
		$report->save();
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.dismisssed", 1, [ 'reports' => 1 ]));
	}
	
	/**
	 * Dismisses all reports for an IP and returns the user back to the reports page.
	 * Handles /report/{report}/dismiss-ip
	 *
	 * @return Response
	 */
	public function getDismissIp(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		$reports = Report::whereOpen()
			->whereResponsibleFor($this->user)
			->where('reporter_ip', $report->reporter_ip)
			->update([
				'is_dismissed'  => true,
				'is_successful' => false,
			]);
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.dismisssed", $reports, [ 'reports' => $reports ]));
	}
	
	/**
	 * Dismisses all reports for a post and returns the user back to the reports page.
	 * Handles /report/{post}/dismiss-post
	 *
	 * @return Response
	 */
	public function getDismissPost(Post $post)
	{
		$reports = $post->reports()
			->whereResponsibleFor($this->user)
			->update([
				'is_dismissed'  => true,
				'is_successful' => false,
			]);
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.dismisssed", $reports, [ 'reports' => $reports ]));
	}
	
	
	public function viewReports()
	{
		return $this->view(static::VIEW_REPORTS, [
			'reportedPosts' => $this->user->getReportedPostsViewable(),
		]);
	}
}