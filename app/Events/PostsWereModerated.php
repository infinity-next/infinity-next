<?php

namespace App\Events;

use App\Ban;
use App\Post;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PostsWereModerated extends Event
{
    use SerializesModels;

    /**
     * The posts affected by this action.
     *
     * @var Illuminate\Support\Collection
     */
    public $posts;

    /**
     * Create a new event instance.
     */
    public function __construct(Collection $posts)
    {
        $this->posts = $posts;

        if ($posts->count() == 1) {
            $post = $posts->get(0);

            if ($post->reply_to) {
                $this->action = "post.delete.reply";
                $this->actionBoard = $post->board_uri;
                $this->actionDetails = [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'op_id' => $post->reply_to_board_id,
                ];
            }
            else {
                $this->action = "post.delete.op";
                $this->actionBoard = $post->board_uri;
                $this->actionDetails = [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'replies' => $post->replies()->count(),
                ];
            }
        }
        else {
            $boardUris = $posts->pluck('board_uri');

            foreach ($boardUris as $boardUri) {
                $this->action = 'post.delete.batch';
                $this->actionBoard = $boardUri;
                $this->actionDetails = [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'ip' => $post->getAuthorIpAsString(),
                    'posts' => $posts->count(),
                ];
            }
        }
    }
}
