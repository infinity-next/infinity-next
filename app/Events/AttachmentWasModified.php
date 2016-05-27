<?php

namespace App\Events;

use App\FileAttachment;
use Illuminate\Queue\SerializesModels;

class AttachmentWasModified extends Event
{
    use SerializesModels;

    /**
     * The post the event is being fired on.
     *
     * @var \App\FileAttachment
     */
    public $attachment;

    /**
     * Create a new event instance.
     */
    public function __construct(FileAttachment $attachment)
    {
        $this->attachment = $attachment;
        $this->post = $attachment->post;
    }
}
