<?php

namespace App\Listeners;

use App\Post;

/**
 * Recounts a thread's replies.
 *
 * @category   Listener
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class ThreadRecount extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        $post = $event->post;

        if ($post instanceof Post) {
            $op = $post->op;

            if (!($op instanceof Post)) {
                $op = $post;
            }

            $op->timestamps = false;
            $op->reply_count = $op->replies()->count();
            $op->reply_file_count = $op->replyFiles()->count();

            $lastReply = $op->replies()->orderBy('created_at', 'desc')->first();

            if (!$lastReply) {
                $lastReply = $op;
            }

            $op->reply_last = $lastReply->created_at;

            // sage threads can't bump; find last bump reply.
            if ($op->post_id != $lastReply->post_id && $lastReply->isBumpless()) {
                $lastBump = $op->replies()
                    ->whereBump()
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$lastBump) {
                    $lastReply = $op;
                } else {
                    $lastReply = $lastBump;
                }
            }

            $op->reply_last = $lastReply->created_at;
        }
    }
}
