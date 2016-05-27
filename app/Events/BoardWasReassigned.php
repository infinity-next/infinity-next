<?php

namespace App\Events;

use App\Board;
use App\Contracts\PermissionUser;
use Illuminate\Queue\SerializesModels;

class BoardWasReassigned extends Event
{
    use SerializesModels;

    /**
     * The board the event is being fired on.
     *
     * @var \App\Board
     */
    public $board;

    /**
     * The user the board has been assigned to.
     *
     * @var \App\Contracts\PermissionUser
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \App\Board                    $board
     * @param \App\Contracts\PermissionUser $user
     */
    public function __construct(Board $board, PermissionUser $user)
    {
        $this->board = $board;
        $this->user = $user;
    }
}
