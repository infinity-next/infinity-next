<?php

namespace App\Policies;

use App\Board;
use App\User;
use Illuminate\Auth\Access\Response;
use Gate;

/**
 * CRUD policy for boards.
 *
 * @category   Policy
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BoardPolicy extends AbstractPolicy
{
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
            : Response::deny();
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
        return ($user->permission('board.user.ban.free', $board) || $user->can('board.user.ban.reason', $board))
            ? Response::allow()
            : Response::deny();
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
            : Response::deny();
    }
}
