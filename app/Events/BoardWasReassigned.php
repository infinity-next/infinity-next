<?php

namespace App\Events;

use App\Board;
use App\Contracts\Auth\Permittable;
use Illuminate\Queue\SerializesModels;

class BoardWasReassigned extends Event
{
    use SerializesModels;

    /**
     * A log name.
     *
     * @var string
     */
    public $action;

    /**
     * Arbitrary log details to be JSON encoded.
     *
     * @var string
     */
    public $actionDetails;

    /**
     * The board the event is being fired on.
     *
     * @var \App\Board
     */
    public $board;

    /**
     * The user the board has been assigned to.
     *
     * @var \App\Contracts\Auth\Permittable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \App\Board                    $board
     * @param \App\Contracts\Auth\Permittable $user
     */
    public function __construct(Board $board, Permittable $user)
    {
        $this->action = "log.board.reassigned";
        $this->actionDetails = null;
        $this->board = $board;
        $this->user = $user;
    }
}
