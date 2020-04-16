<?php

namespace App\Jobs;

use App\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Rebuilds thread caches after a new post.
 *
 * @category   Job
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @see \Illuminate\Auth\Middleware\Authenticate
 *
 * @since      0.6.0
 */
class ThreadAutoprune extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
     * @param  Post  $post
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $post = $this->post;

        if (!$post->isOp()) {
            $post = $post->thread;
        }

        $replyCount = $post->replies()->count();
        $board = $post->board;
        $now = now();
        $modified = false;


        // Bumplock the thread after the nth reply
        $sageOnReply = $board->getConfig('epheSageThreadReply');

        if ($sageOnReply > 0 && $replyCount >= $sageOnReply && is_null($post->bumplocked_at)) {
            $modified = true;
            $post->bumplocked_at = $now;
        }

        // Lock the thread after the nth reply
        $lockOnReply = $board->getConfig('epheLockThreadReply');

        if ($lockOnReply > 0 && $replyCount >= $lockOnReply && is_null($post->locked_at)) {
            $modified = true;
            $post->locked_at = $now;
        }

        // Delete thread after the nth reply
        $deleteOnReply = $board->getConfig('epheDeleteThreadReply');

        if ($deleteOnReply > 0 && $replyCount >= $deleteOnReply && is_null($post->deleted_at)) {
            $modified = true;
            $post->deleted_at = $now;
        }


        if ($modified) {
            $post->save();
        }
    }
}
