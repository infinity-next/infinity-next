<?php

namespace App\Events;

use App\Board;
use App\Log;
use App\Contracts\Auth\Permittable;
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
     * Create a new event instance.
     */
    public function __construct(Board $board, Permittable $user)
    {
        $this->action = "board.create";
        $this->board = $board;
        $this->user = $user;
    }
}
