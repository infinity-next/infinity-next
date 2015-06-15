<?php namespace App\Events;

use App\Post;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PostWasDeleted extends Event
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
	 *
	 * @return void
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
	}
}
