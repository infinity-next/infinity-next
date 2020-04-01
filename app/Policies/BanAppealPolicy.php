<?php

namespace App\Policies;

use App\Board;
use App\BanAppeal;
use App\Contracts\Auth\Permittable as User;
use Illuminate\Auth\Access\Response;
use Gate;

/**
 * CRUD policy for appeals.
 *
 * @category   Policy
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class BanAppealPolicy extends AbstractPolicy
{
    /**
     * Can this user approve or deny this appeal?
     *
     * @param  \App\User       $user
     * @param  \App\BanAppeal  $appeal
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function manage(User $user, BanAppeal $appeal)
    {
        return $user->permission('board.user.unban', $appeal->board);
    }

    /**
     * Can this user view this appeal?
     *
     * @param  \App\User       $user
     * @param  \App\BanAppeal  $appeal
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function view(User $user, BanAppeal $appeal)
    {
        return $user->permission('board.user.unban', $appeal->board);
    }

    /**
     * Can this user view appeal on any board?
     *
     * @param  \App\User       $user
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return ($user->permission('board.user.unban') || $user->permissionAny('board.user.unban'));
    }

    /**
     * Can this user view appeals for global bans?
     *
     * @param  \App\User       $user
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function viewGlobal(User $user)
    {
        return $user->permission('board.user.unban');
    }
}
