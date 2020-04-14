<?php

namespace App\Broadcasting;

class ThreadChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @return array|bool
     */
    public function join()
    {
        return user();
    }
}
