<?php

namespace App\Policies;

use App\Ban;
use App\Contracts\Auth\Permittable as User;
use App\Support\IP;
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
class BanPolicy extends AbstractPolicy
{
    /**
     * Can this user view this ban?
     *
     * @param  \App\User  $user
     * @param  \App\Ban   $appeal
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function appeal(User $user, Ban $ban)
    {
        if (!$ban->isBanForIP() || $ban->isShort()) {
            return Response::deny();
        }

        return Response::allow();
    }

    /**
     * Can this user view this ban?
     *
     * @param  \App\User  $user
     * @param  \App\Ban   $appeal
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function view(User $user, Ban $ban)
    {
        if ($user->permission('board.user.ban', $ban->board)) {
            return Response::deny();
        }

        if (!$ban->isBanForIP()) {
            return Response::deny();
        }

        return Response::allow();
    }
}
