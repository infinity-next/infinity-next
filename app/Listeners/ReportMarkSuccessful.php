<?php

namespace App\Listeners;

use App\Report;

class ReportMarkSuccessful extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if (isset($event->post)) {
            $reports = $event->post->reports()
                ->where('is_dismissed', false)
                ->where('is_successful', false)
                ->update(['is_successful' => true]);
        }

        if (isset($event->posts)) {
            $postIds = $event->posts->pluck('post_id');
            Report::whereIn('post_id', $postIds)
                ->where('is_dismissed', false)
                ->where('is_successful', false)
                ->update([ 'is_successful' => true ]);
        }
    }
}
