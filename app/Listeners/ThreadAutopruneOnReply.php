<?php namespace App\Listeners;

use App\Post;
use App\Listeners\Listener;
use Cache;
use Carbon\Carbon;

class ThreadAutopruneOnReply extends Listener
{
	
	/**
	 * Handle the event.
	 *
	 * @param  Event  $event
	 * @return void
	 */
	public function handle($event)
	{
		if (isset($event->post) && $event->post instanceof Post)
		{
			$post       = $event->post;
			
			if (!$event->post->isOp())
			{
				$post = $post->op;
			}
			
			$replyCount = $post->replies()->count();
			$board      = $post->board;
			$now        = Carbon::now();
			$modified   = false;
			
			
			// Bumplock the thread after the nth reply
			$sageOnReply     = $board->getConfig('epheSageThreadReply');
			
			if ($sageOnReply > 0 && $replyCount >= $sageOnReply && is_null($post->bumplocked_at))
			{
				$modified = true;
				$post->bumplocked_at = $now;
			}
			
			// Lock the thread after the nth reply
			$lockOnReply     = $board->getConfig('epheLockThreadReply');
			
			if ($lockOnReply > 0 && $replyCount >= $lockOnReply && is_null($post->locked_at))
			{
				$modified = true;
				$post->locked_at     = $now;
			}
			
			// Delete thread after the nth reply
			$deleteOnReply   = $board->getConfig('epheDeleteThreadReply');
			
			if ($deleteOnReply > 0 && $replyCount >= $deleteOnReply && is_null($post->deleted_at))
			{
				$modified = true;
				$post->deleted_at    = $now;
			}
			
			
			if ($modified)
			{
				$post->save();
			}
		}
	}
	
}