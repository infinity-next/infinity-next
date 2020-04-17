<?php

namespace App\Events;

use App\Post;
use Illuminate\Queue\SerializesModels;

/**
 * Post was edited by a moderator.
 *
 * @category   Events
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class PostWasEdited extends Event
{
    use SerializesModels;

    /**
     * The post the event is being fired on.
     *
     * @var \App\Post
     */
    public $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
        $this->action = 'post.edit';
        $this->actionBoard = $post->board_uri;
        $this->actionDetails = [
            'board_id' => $post->board_id,
            'board_uri' => $post->board_uri,
        ];
    }
}
