<?php

namespace App\Events;

use App\Ban;
use App\Post;
use Illuminate\Queue\SerializesModels;

class BanWasCreated extends Event
{
    use SerializesModels;

    /**
     * The ban created.
     *
     * @var \App\Ban
     */
    public $ban;

    /**
     * Create a new event instance.
     */
    public function __construct(Ban $ban)
    {
        $this->ban = $ban;
        $this->ip = $ban->getCidr();

        $this->action = 'post.ban.' . ($ban->isGlobal() ? 'global' : 'local');
        $this->actionBoard = $ban->board_uri;
        $this->actionDetails = [
            'board_uri' => $ban->board_uri,
            'ip' => (string)$this->ip,
            'justification' => $ban->justification,
            'time' => $ban->getDurationForHumans(),
        ];
        $this->user = $ban->mod;
    }
}
