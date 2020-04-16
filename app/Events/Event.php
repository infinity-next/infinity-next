<?php

namespace App\Events;

abstract class Event
{
    /**
     * Log name
     *
     * @var string.
    */
    public $action = null;

    /**
     * Board URI or null for global.
     *
     * @var string
     */
    public $actionBoard = null;

    /**
     * Arbitrary log details to be JSON encoded.
     *
     * @var string
     */
    public $actionDetails = null;

    /**
     * The board the event is being fired on.
     *
     * @var \App\Support\IP
     */
    public $ip;

    /**
     * The board the event is being fired on.
     *
     * @var \App\Auth\Permittable
     */
    public $user;
}
