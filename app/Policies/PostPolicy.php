<?php

namespace App\Policies;

use App\Board;
use App\Post;
use App\User;
use Illuminate\Auth\Access\Response;

/**
 * CRUD policy for posts.
 *
 * @category   Policy
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class PostPolicy extends AbstractPolicy
{
    /**
     * Can this user attach authorship to a post?
     *
     * @param  \App\User  $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function author(User $user, Board $board)
    {
        return $board->getConfig('postsAllowAuthor')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Can this user prevent this post from being bumped?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function bumplock(User $user, Post $post)
    {
        // This only applies to OPs.
        if (!is_null($post->reply_to))
            return Response::deny('auth.post.only_on_an_op');

        if ($user->can('board.post.bumplock', $post->board_uri))
            return Response::allow();

        return Response::deny();
    }

    /**
     * Can this user ban the author of this post?
     *
     * @see BoardPolicy::ban()
     *
     * @param  \App\User  $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function ban(User $user, Post $post)
    {
        if (!$post->hasAuthorIp())
            return Response::deny('auth.post.cannot_ban_without_ip');

        return $user->can('ban', $post->board)
            ? Response::allow()
            : Response::deny();
    }


    /**
     * Can this user reply to this post?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function create(User $user, Board $board)
    {
        return $user->permission('board.post.create.thread', $board)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Can this user supply passwords when creating a post?
     *
     * @param  \App\User   $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function password(User $user, Board $board)
    {
        // Previously, this would refuse if the user could not subsequently
        // do anything with his password (like delete the post with it).
        // However, it makes more sense to always allow the password in case
        // the permissions change later.
        return Response::allow();
    }

    /**
     * Can this user reply to this post?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function reply(User $user, Post $post)
    {
        if ($post->isLocked() && !$user->can('lock', $post)) {
            return Response::deny('auth.post.thread_is_locked');
        }

        return $user->permission('board.post.create.reply', $post->board)
            ? Response::allow()
            : Response::deny('auth.post.cannot_reply');
    }

    /**
     * Can this user give a subject for a post?
     *
     * @param  \App\User  $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function subject(User $user, Board $board, Post $post = null)
    {
        if ($board->getConfig('postsAllowSubject')) {
            return Response::allow();
        }

        if ($board->getConfig('threadRequireSubject') && is_null($post)) {
            return Response::allow();
        }

        return Response::deny();
    }

    /**
     * Can this user edit this post?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function update(User $user, Post $post)
    {
        ## TODO ##
        return Resposne::deny();
    }
}
