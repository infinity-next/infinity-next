<?php namespace App\Http\Requests;

use App\Board;
use App\Contracts\PermissionUser;

trait RequestAcceptsUserAndBoard {
	
	/**
	 * Current Board set by controller.
	 *
	 * @var Board
	 */
	protected $board;
	
	/**
	 * Current Board set by controller.
	 *
	 * @var PermissionUser|Support\Anonymous
	 */
	protected $user;
	
	/**
	 * Returns the request's current board.
	 *
	 * @return Board
	 */
	public function getBoard()
	{
		return $this->board;
	}
	
	/**
	 * Returns the request's current user.
	 *
	 * @return User|Support\Anonymous
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * Sets the request's board.
	 *
	 * @param  Board  $board
	 * @return void
	 */
	public function setBoard(Board $board)
	{
		$this->board = $board;
	}
	
	/**
	 * Returns the request's user.
	 *
	 * @param  PermissionUser  $user
	 * @return void
	 */
	public function setUser(PermissionUser $user)
	{
		$this->user = $user;
	}
	
}