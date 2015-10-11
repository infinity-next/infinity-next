<?php namespace App\Events;

use App\Post;
use App\User;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PostWasModerated extends Event
{
	use SerializesModels;
	
	/**
	 * The post the event is being fired on.
	 *
	 * @var \App\Post
	 */
	public $post;
	
	/**
	 * The user that caused this event.
	 *
	 * @var \App\User
	 */
	public $user;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(Post $post, User $user)
	{
		$this->moderator = $user;
		$this->post = $post;
	}
}
