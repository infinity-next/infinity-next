<?php

namespace App\Policies;

use App\Board;
use App\Option;
use App\Contracts\Auth\Permittable as User;
use Illuminate\Auth\Access\Response;
use Gate;

/**
 * CRUD policy for boards.
 *
 * @category   Policy
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BoardPolicy extends AbstractPolicy
{
    /**
     * Can this add existing attachments to their posts?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function attach(User $user, Board $board)
    {
        // The only thing we care about for this setting is the permission mask.
        return $user->permission('board.attachment.upload', $board)
            ? Response::allow()
            : Response::deny('auth.board.cannot_attach');
    }

    /**
     * Can this user ban an IP address from this board?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function audit(User $user, Board $board)
    {
        return $user->permission('board.logs', $board)
            ? Response::allow()
            : Response::deny('auth.board.cannot_audit');
    }

    /**
     * Can this user ban an IP address from this board?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function ban(User $user, Board $board)
    {
        return ($user->permission('board.user.ban.free', $board) || $user->permission('board.user.ban.reason', $board))
            ? Response::allow()
            : Response::deny('auth.board.cannot_ban');
    }

    /**
     * Can this user create a board?
     *
     * @param  \App\User  $user
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function create(User $user)
    {
        return $user->permission('board.create')
            ? Response::allow()
            : Response::deny('auth.board.cannot_create');
    }

    /**
     * Can this user configure a board?
     *
     * @param  \App\User        $user
     * @param  \App\Board|null  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function configure(User $user, ?Board $board = null)
    {
        if ($board instanceof Board) {
            return $user->permission('board.config', $board)
                ? Response::allow()
                : Response::deny('auth.board.cannot_config');
        }

        return $user->permissionAny('board.config')
            ? Response::allow()
            : Response::deny('auth.board.cannot_config');
    }

    /**
     * Can this user edit a board's uri?
     *
     * @param  \App\User  $user
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function editUri(User $user)
    {
        return Response::deny("auth.board.cannot_edit_uri");
    }

    /**
     * Can this user modify another user's permissions on this board?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     * @param  \App\User   $target
     *
     * @return Illuminate\Auth\Access\Response
     */
     public function editStaff(User $user, Board $board, User $target)
     {
         if ($user->user_id == $target->user_id) {
             return Response::deny("auth.board.cann_edit_own_permissions");
         }

         return $user->can('admin-config');
     }

    /**
     * Can this user view posting history on this board?
     *
     * @param  \App\User  $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function history(User $user, Board $board)
    {
        return $user->permission('board.history', $board)
            ? Response::allow()
            : Response::deny('auth.board.cannot_view_history');
    }

    /**
     * Can this user create a post?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function post(User $user, Board $board)
    {
        return $user->permission('board.post.create.thread', $board)
            ? Response::allow()
            : Response::deny('auth.post.cannot_post');
    }

    public function settingEdit(User $user, ?Board $board = null, ?Option $option = null)
    {
        if (is_null($board)) {
            return Response::deny();
        }

        if (!is_null($option) && $option->isLocked()) {
            return $this->permission('site.board.setting_lock')
             ? Response::allow()
             : Response::deny('auth.site.setting_locked');
        }

        return $user->can('configure', $board);
    }
}
