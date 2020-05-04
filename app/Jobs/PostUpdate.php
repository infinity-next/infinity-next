<?php

namespace App\Jobs;

use App\Post;
use App\Events\PostWasEdited;
use App\Events\PostWasModified;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostUpdate implements ShouldQueue
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
        // Fire an edited event if a user applied an edit
        if ($this->post->isDirty('updated_by')) {
            $event = new PostWasEdited($this->post);
            $event->user = user();
            $event->ip = new IP;
            event($event);
        }

        // Fire event, which clears cache among other things.
        broadcast(new PostWasModified($this->post));
    }
}
