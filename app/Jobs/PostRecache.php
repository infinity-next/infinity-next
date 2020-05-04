<?php

namespace App\Jobs;

use App\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;

class PostRecache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $posts;

    /**
     * Create a new job instance.
     *
     * @param  array  $posts  Array of post_ids, check PostObserver
     *
     * @return void
     */
    public function __construct(array $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $posts = Post::whereIn('post_id', $this->posts)->withEverything()->get();

        foreach ($posts as $post) {
            Cache::tags(["post_{$post->post_id}"])->flush();
            Cache::tags([
                "board_{$post->board_uri}",
                "board_id_{$post->board_id}",
            ])->flush();
        }
    }
}
