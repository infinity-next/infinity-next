<?php

namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Post;
use App\Report;
use App\Http\Controllers\Panel\PanelController;
use Request;

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
class ReportController extends PanelController
{
    const VIEW_REPORTS = 'panel.report.index';

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
    public function index()
    {
        if (Request::wantsJson()) {
            return Post::whereHasReportsFor(user())
                ->withEverythingForReplies()
                ->with('reports')
                ->get();
        }

        return $this->makeView(static::VIEW_REPORTS, [
            'reportedPosts' => user()->getReportedPostsViewable(),
        ]);
    }

    /**
     * Demotes a single report and returns the user back to the reports page.
     * Handles /report/{report}/demote.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function demote(Report $report)
    {
        $this->authorize('demote', $report);

        if (!$report->isOpen()) {
            abort(404);
        }

        $report->global = false;
        $report->promoted_at = $report->freshTimestamp();
        $report->promoted_by = user()->user_id;
        $report->save();

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.demoted', 1, ['reports' => 1]));
    }

    /**
     * Demotes a single report and returns the user back to the reports page.
     * Handles /report/{post}/demote-post.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function demotePost(Post $post)
    {
        $this->authorize('demote', [Report::class, $report->board]);

        $reports = $post->reports()
            ->whereResponsibleFor(user())
            ->update([
                'global' => false,
                'promoted_at' => $post->freshTimestamp(),
                'promoted_by' => user()->user_id,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.demoted', $reports, ['reports' => $reports]));
    }

    /**
     * Dismisses a single report and returns the user back to the reports page.
     * Handles /report/{report}/dismiss.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function dismiss(Report $report)
    {
        $this->authorize('dismiss', $report);

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
     * Dismisses all reports for an IP and returns the user back to the reports page.
     * Handles /report/{report}/dismiss-ip.
     *
     * @return Response
     */
    public function dissmissIp(Report $report)
    {
        $this->authorize('dismiss', $report);

        $reports = Report::whereOpen()
            ->whereResponsibleFor(user())
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
    public function dismissPost(Post $post)
    {
        $this->authorize('dismiss', $report);

        $reports = $post->reports()
            ->whereResponsibleFor(user())
            ->update([
                'is_dismissed' => true,
                'is_successful' => false,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.dismisssed', $reports, ['reports' => $reports]));
    }

    /**
     * Promotes a single report and returns the user back to the reports page.
     * Handles /report/{report}/promote.
     *
     * @param Report $report
     *
     * @return Response
     */
    public function promote(Report $report)
    {
        $this->authorize('promote', $report);

        if (!$report->isOpen()) {
            abort(404);
        }

        $report->global = true;
        $report->promoted_at = $report->freshTimestamp();
        $report->promoted_by = user()->user_id;
        $report->save();

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.promoted', 1, ['reports' => 1]));
    }

    /**
     * Promotes a single report and returns the user back to the reports page.
     * Handles /report/{post}/promote-post.
     *
     * @param Post $post
     *
     * @return Response
     */
    public function promotePost(Post $post)
    {
        $this->authorize('createGlobal', [Report::class, $report->board]);

        $reports = $post->reports()
            ->whereResponsibleFor(user())
            ->update([
                'global' => true,
                'promoted_at' => $post->freshTimestamp(),
                'promoted_by' => user()->user_id,
            ]);

        return redirect()->back()
            ->withSuccess(trans_choice('panel.reports.promoted', $reports, ['reports' => $reports]));
    }
}
