<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Post;
use App\Report;
use App\Http\Controllers\Panel\PanelController;

/**
 * Lists and manages content reports.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class ReportsController extends PanelController
{
    const VIEW_REPORTS = 'panel.board.reports';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.board';

    /**
     * Displays a full list of reports.
     * Handles /reports/.
     *
     * @return Response
     */
    public function getIndex()
    {
        return $this->view(static::VIEW_REPORTS, [
            'reportedPosts' => user()->getReportedPostsViewable(),
        ]);
    }

    /**
     * Dismisses a single report and returns the user back to the reports page.
     * Handles /report/{report}/dismiss.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function getDismiss(Report $report)
    {
        $this->authorize('report', $report);

        if (!$report->isOpen()) {
            abort(404);
        }

        $report->is_dismissed = true;
        $report->is_successful = false;
        $report->save();

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.dismisssed', 1, ['reports' => 1]));
    }

    /**
     * Promotes a single report and returns the user back to the reports page.
     * Handles /report/{report}/promote.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function getPromote(Report $report)
    {
        $this->authorize('promote', $report);

        if (!$report->isOpen()) {
            abort(404);
        }

        $report->global = true;
        $report->promoted_at = $report->freshTimestamp();
        $report->promoted_by = $this->user->user_id;
        $report->save();

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.promoted', 1, ['reports' => 1]));
    }

    /**
     * Demotes a single report and returns the user back to the reports page.
     * Handles /report/{report}/demote.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function getDemote(Report $report)
    {
        $this->authorize('demote', $report);

        if (!$report->isOpen()) {
            abort(404);
        }

        $report->global = false;
        $report->promoted_at = $report->freshTimestamp();
        $report->promoted_by = $this->user->user_id;
        $report->save();

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.demoted', 1, ['reports' => 1]));
    }

    /**
     * Dismisses all reports for an IP and returns the user back to the reports page.
     * Handles /report/{report}/dismiss-ip.
     *
     * @return Response
     */
    public function getDismissIp(Report $report)
    {
        $this->authorize('dismiss', $report);

        $reports = Report::whereOpen()
            ->whereResponsibleFor($this->user)
            ->where('reporter_ip', $report->reporter_ip)
            ->update([
                'is_dismissed' => true,
                'is_successful' => false,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.dismisssed', $reports, ['reports' => $reports]));
    }

    /**
     * Dismisses all reports for a post and returns the user back to the reports page.
     * Handles /report/{post}/dismiss-post.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function getDismissPost(Post $post)
    {
        $this->authorize('dismiss', $report);

        $reports = $post->reports()
            ->whereResponsibleFor($this->user)
            ->update([
                'is_dismissed' => true,
                'is_successful' => false,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.dismisssed', $reports, ['reports' => $reports]));
    }

    /**
     * Promotes a single report and returns the user back to the reports page.
     * Handles /report/{post}/promote-post.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function getPromotePost(Post $post)
    {
        $this->authorize('createGlobal', [Report::class, $report->board]);

        $reports = $post->reports()
            ->whereResponsibleFor($this->user)
            ->update([
                'global' => true,
                'promoted_at' => $post->freshTimestamp(),
                'promoted_by' => $this->user->user_id,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.promoted', $reports, ['reports' => $reports]));
    }

    /**
     * Demotes a single report and returns the user back to the reports page.
     * Handles /report/{post}/demote-post.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function getDemotePost(Post $post)
    {
        $this->authorize('demote', [Report::class, $report->board]);

        $reports = $post->reports()
            ->whereResponsibleFor($this->user)
            ->update([
                'global' => false,
                'promoted_at' => $post->freshTimestamp(),
                'promoted_by' => $this->user->user_id,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.demoted', $reports, ['reports' => $reports]));
    }
}
