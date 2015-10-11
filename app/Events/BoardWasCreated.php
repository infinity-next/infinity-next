<?php namespace App\Events;

use App\Board;
use App\Events\Event;
use App\Contracts\PermissionUser;
use Illuminate\Queue\SerializesModels;

class BoardWasCreated extends Event
{
	use SerializesModels;
	
	/**
	 * The board the event is being fired on.
	 *
	 * @var \App\Board
	 */
	public $board;
	
	/**
	 * The board the event is being fired on.
	 *
	 * @var \App\Traits\PermissionUser
	 */
	public $User;
	
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(Board $board, PermissionUser $user)
	{
		$this->board = $board;
		$this->user  = $user;
	}
}
