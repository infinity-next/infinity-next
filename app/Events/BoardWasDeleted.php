<?php

namespace App\Events;

use App\Board;
use App\Log;
use App\Contracts\Auth\Permittable;
use Illuminate\Queue\SerializesModels;

class BoardWasDeleted extends Event
{
    use SerializesModels;

    /**
     * The board the event is being fired on.
     *
     * @var \App\Board
     */
    public $board;

    /**
     * Create a new event instance.
     */
    public function __construct(Board $board)
    {
        $this->action = "board.delete";
        $this->board = $board;
        $this->user = user();
    }
}
