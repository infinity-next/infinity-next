<?php namespace App\Events;

use App\Post;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class ThreadWasStickied extends Event
{
	use SerializesModels;
	
	/**
	 * The post the event is being fired on.
	 *
	 * @var \App\Post
	 */
	public $post;
	
	/**
	 * The board page which must be cleared as a result of this event.
	 *
	 * @var integer|true
	 */
	public $page;
	
	/**
	 * Create a new event instance.
	 *
	 * @param  \App\Post  $post
	 * @return void
	 */
	public function __construct(Post $post)
	{
		$this->post = $post;
		
		## TODO ##
		// Make this only clear the pages on or before the bump page.
		$this->page = true;
	}
}
