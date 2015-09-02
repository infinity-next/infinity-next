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
	 * @param  Report  $report
	 * @return Response
	 */
	public function getDismiss(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		if (!$report->isOpen())
		{
			abort(404);
		}
		
		$report->is_dismissed = true;
		$report->is_successful = false;
		$report->save();
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.dismisssed", 1, [ 'reports' => 1 ]));
	}
	
	/**
	 * Promotes a single report and returns the user back to the reports page.
	 * Handles /report/{report}/promote
	 *
	 * @param  Report  $report
	 * @return Response
	 */
	public function getPromote(Report $report)
	{
		if (!$this->user->canReportGlobally() || !$report->canView($this->user))
		{
			abort(403);
		}
		
		if (!$report->isOpen())
		{
			abort(404);
		}
		
		$report->global = true;
		$report->promoted_at = $report->freshTimestamp();
		$report->promoted_by = $this->user->user_id;
		$report->save();
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.promoted", 1, [ 'reports' => 1 ]));
	}
	
	/**
	 * Demotes a single report and returns the user back to the reports page.
	 * Handles /report/{report}/demote
	 *
	 * @param  Report  $report
	 * @return Response
	 */
	public function getDemote(Report $report)
	{
		if (!$report->canView($this->user))
		{
			abort(403);
		}
		
		if (!$report->isOpen())
		{
			abort(404);
		}
		
		$report->global = false;
		$report->promoted_at = $report->freshTimestamp();
		$report->promoted_by = $this->user->user_id;
		$report->save();
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.demoted", 1, [ 'reports' => 1 ]));
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
	 * @param  Post  $post
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
	
	/**
	 * Promotes a single report and returns the user back to the reports page.
	 * Handles /report/{post}/promote-post
	 *
	 * @param  Post  $post
	 * @return Response
	 */
	public function getPromotePost(Post $post)
	{
		if (!$this->user->canReportGlobally($post))
		{
			abort(403);
		}
		
		$reports = $post->reports()
			->whereResponsibleFor($this->user)
			->update([
				'global'      => true,
				'promoted_at' => $post->freshTimestamp(),
				'promoted_by' => $this->user->user_id,
			]);
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.promoted", $reports, [ 'reports' => $reports ]));
	}
	
	/**
	 * Demotes a single report and returns the user back to the reports page.
	 * Handles /report/{post}/demote-post
	 *
	 * @param  Post  $post
	 * @return Response
	 */
	public function getDemotePost(Post $post)
	{
		if (!$this->user->canViewReportsGlobally())
		{
			abort(403);
		}
		
		$reports = $post->reports()
			->whereResponsibleFor($this->user)
			->update([
				'global'      => false,
				'promoted_at' => $post->freshTimestamp(),
				'promoted_by' => $this->user->user_id,
			]);
		
		return redirect()->back()
			->withSuccess(trans_choice("panel.reports.demoted", $reports, [ 'reports' => $reports ]));
	}
	
	
	public function viewReports()
	{
		return $this->view(static::VIEW_REPORTS, [
			'reportedPosts' => $this->user->getReportedPostsViewable(),
		]);
	}
}