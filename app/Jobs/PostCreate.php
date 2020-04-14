<?php

namespace App\Jobs;

use App\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostCreate extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;

    /**
     * Create a new job instance.
     *
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

        // Fire event, which clears cache among other things.
        event(new \App\Events\PostWasCreated($post));

        // Log staff posts.
        if ($post->capcode_id) {
            event(new \App\Events\PostWasCapcoded($post, user()));
        }

        // Finally fire event on OP, if it exists.
        if (!is_null($post->reply_to)) {
            broadcast(new \App\Events\ThreadReply($post->thread, $post));
        }
    }
}
