<?php

namespace App\Policies;

use App\Board;
use App\Post;
use App\Contracts\Auth\Permittable as User;
use Illuminate\Auth\Access\Response;
use Gate;

/**
 * CRUD policy for reports.
 *
 * @category   Policy
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class ReportPolicy extends AbstractPolicy
{
    /**
     *
     * Can this user create reports?
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function create(?User $user, Board $board)
    {
        return $user->permission('board.post.report', $board)
            ? Response::allow()
            : Response::deny('auth.report.cannot_create');
    }

    /**
     * Can this user create reports to this post to site admins?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function createGlobal(?User $user, Board $board)
    {
        return $user->permission('site.post.report', $board)
            ? Response::allow()
            : Response::deny('auth.report.cannot_create_global');
    }

    /**
     * Can this user demote this report back to the board?
     *
     * @param App\User    $user
     * @param App\Report  $report
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function demote(?User $user, Report $report)
    {
        if ($report->isDemoted()) {
            return Response::deny('auth.report.already_demoted');
        }

        if (!$report->global) {
            return Response::deny('auth.report.not_global');
        }

        return $user->can('view', $report);
    }

    /**
     * Can this user dismiss this report?
     *
     * @param App\User    $user
     * @param App\Report  $report
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function dismiss(?User $user, Report $report)
    {
        // At the moment, anyone who can view can dismiss.
        return $user->can('view', $report);
    }

    /**
     * Can this user promote this report to global scope?
     *
     * @param App\User    $user
     * @param App\Report  $report
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function promote(?User $user, Report $report)
    {
        if ($report->isPromoted()) {
            return Response::deny('auth.report.already_promoted');
        }

        if ($report->global) {
            return Response::deny('auth.report.not_local');
        }

        return $user->can('create-global', $report);
    }

    /**
     * Can this user view this report?
     *
     * @param App\User    $user
     * @param App\Report  $report
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function view(?User $user, Report $report)
    {
        // If this is a global report, we check for that permission.
        if ($report->global) {
            return $user->permission('board.reports', $report->board_uri)
                ? Response::allow()
                : Response::deny('auth.site.cannot_view_reports');
        }

        return $user->permissionAny('board.reports')
            ? Response::allow()
            : Response::deny('auth.board.cannot_view_reports');
    }

    /**
     * Can this user view reports to site admins?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function viewGlobal(?User $user)
    {
        return $user->permission('site.reports')
            ? Response::allow()
            : Response::deny('auth.site.cannot_view_reports');
    }
}
