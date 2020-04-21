<?php

namespace App\Policies;

use App\Board;
use App\Post;
use App\Contracts\Auth\Permittable as User;
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
            : Response::deny('auth.post.cannot_use_authors');
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
        if (!is_null($post->reply_to)) {
            return Response::deny('auth.post.only_on_an_op');
        }

        if ($user->permission('board.post.bumplock', $post)) {
            return Response::allow();
        }

        return Response::deny('auth.post.cannot_bumplock');
    }

    /**
     * Can this user ban the author of this post?
     *
     * @see BoardPolicy::ban()
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function ban(User $user, Post $post)
    {
        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return $user->can('ban', $post->board)
            ? Response::allow()
            : Response::deny('auth.post.cannot_ban');
    }

    /**
     * Can this user delete this post?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function delete(User $user, Post $post)
    {
        // If we can edit any post for this board ...
        if ($user->permission('board.post.delete.other', $post)) {
            return Response::allow();
        }

        // If the author and our current user share an IP ...
        //if (!is_null($post->author_ip) && $post->author_ip->is(Request::ip())) {
            // Allow post edit, if the masks allows it.
            //return $this->permission('board.post.edit.self', $post->attributes['board_uri']);
        //}

        return Response::deny('auth.post.cannot_without_password');
    }

    /**
     * Can this user delete all other posts by this IP on this board?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function deleteHistory(User $user, Post $post)
    {
        if (!$user->can('delete', $post)) {
            return $this->delete($user, $post);
        }

        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return Response::allow();
    }

    /**
     * Can this user reply to this post?
     *
     * @param  \App\User   $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function edit(User $user, Post $post)
    {
        if ($post->body_signed) {
            return Response::deny('auth.post.cannot_edit_signed_message');
        }

        // If we can edit any post for this board ...
        if ($user->permission('board.post.edit.other', $post)) {
            return Response::allow();
        }

        // If the author and our current user share an IP ...
        //if (!is_null($post->author_ip) && $post->author_ip->is(Request::ip())) {
            // Allow post edit, if the masks allows it.
            //return $this->permission('board.post.edit.self', $post->attributes['board_uri']);
        //}

        return Response::deny('auth.post.cannot_without_password');
    }

    /**
     * Can this user feature a post across the site?
     *
     * @see BoardPolicy::ban()
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function feature(User $user, Post $post)
    {
        return $user->permission('sys.config')
            ? Response::allow()
            : Response::deny('auth.post.cannot_feature');
    }

    /**
     * Can this user view this poster's other posts on this board?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function history(User $user, Post $post)
    {
        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return $user->can('history', $post->board);
    }

    /**
     * Can this user delete this poster's other posts across the site?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function globalBan(User $user, Post $post)
    {
        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return $user->can('global-ban');
    }

    /**
     * Can this user delete this poster's other posts across the site?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function globalDelete(User $user, Post $post)
    {
        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return $user->can('global-delete');
    }

    /**
     * Can this user view this poster's other posts across the site?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function globalHistory(User $user, Post $post)
    {
        if (!$post->hasAuthorIp()) {
            return Response::deny('auth.post.no_ip_address');
        }

        return $user->can('global-history');
    }

    /**
     * Can this user lock threads?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function lock(User $user, Post $post)
    {
        // This only applies to OPs.
        if (!is_null($post->reply_to)) {
            return Response::deny('auth.post.only_on_an_op');
        }

        return $user->permission('board.post.lock', $post)
            ? Response::allow()
            : Response::deny('auth.post.cannot_lock');
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
     * Can this user report to this post to board moderators?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function report(User $user, Post $post)
    {
        return $user->permission('board.post.report', $post)
            ? Response::allow()
            : Response::deny('auth.post.cannot_report');
    }

    /**
     * Can this user report to this post to site admins?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function reportGlobal(User $user, Post $post)
    {
        return $user->permission('site.post.report', $post)
            ? Response::allow()
            : Response::deny('auth.post.cannot_report_global');
    }

    /**
     * Can this user sticky this post to the board?
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function sticky(User $user, Post $post)
    {
        // This only applies to OPs.
        if (!is_null($post->reply_to)) {
            return Response::deny('auth.post.only_on_an_op');
        }

        return $user->permission('board.post.sticky', $post)
            ? Response::allow()
            : Response::deny('auth.post.cannot_sticky');
    }

    /**
     * Can this user give a subject for a post?
     *
     * @param  \App\User  $user
     * @param  \App\Board  $board
     *
     * @return Illuminate\Auth\Access\Response
     */
    public function subject(User $user, Board $board, ?Post $post = null)
    {
        if ($board->getConfig('postsAllowSubject')) {
            return Response::allow();
        }

        if ($board->getConfig('threadRequireSubject') && is_null($post)) {
            return Response::allow();
        }

        return Response::deny('auth.post.cannot_use_subjects');
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
        return Resposne::deny('auth.post.cannot_edit');
    }
}
