<?php

namespace App\Events;

use App\PostAttachment;
use Illuminate\Queue\SerializesModels;

class AttachmentWasModified extends Event
{
    use SerializesModels;

    /**
     * The post the event is being fired on.
     *
     * @var \App\PostAttachment
     */
    public $attachment;

    /**
     * Create a new event instance.
     */
    public function __construct(PostAttachment $attachment)
    {
        $this->attachment = $attachment;
        $this->post = $attachment->post;
    }
}
