<?php

namespace App\Events;

use App\Board;
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
     * The board the event is being fired on.
     *
     * @var \App\Auth\Permittable
     */
    public $User;

    /**
     * Create a new event instance.
     */
    public function __construct(Board $board, Permittable $user)
    {
        $this->board = $board;
        $this->user = $user;
    }
}
