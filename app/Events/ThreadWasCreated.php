<?php namespace App\Events;

use App\Post;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ThreadWasCreated extends Event
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
	 * @param  \App\Post  $post
	 * @return void
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
	}
}
