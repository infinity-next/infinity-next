<?php namespace App\Console\Commands;

use App\Board;

use Illuminate\Console\Command;

use Carbon\Carbon;

class Autoprune extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'autoprune';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Automatically prune threads based on time elapsed';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$carbonNow = Carbon::now();
		
		// Load all of our boards with their settings and threads, 100 at a time.
		Board::with([
			'settings',
			'threads' => function($query)
			{
				$query->whereNull('reply_to');
				$query->orderBy('bumped_last', 'desc');
			},
		])->chunk(100, function($boards) use ($carbonNow) {
			
			$this->comment("    Pruning 100 boards...");
			
			// With each board, fetch their autoprune settings.
			foreach ($boards as $board)
			{
				// Get important settings.
				$threadsPerPage = (int) $board->getSetting('postsPerPage', 10);
				
				// Collect a list of threads which have been modified.
				$threadsToSave  = [];
				
				
				// There are two groups of autoprune settings.
				// x on day since last reply
				$sageOnDay    = (int) $board->getSetting('epheSageThreadDays');
				$lockOnDay    = (int) $board->getSetting('epheLockThreadDays');
				$deleteOnDay  = (int) $board->getSetting('epheDeleteThreadDays');
				
				// x on page (meaning the thread has fallen to this page)
				$sageOnPage   = (int) $board->getSetting('epheSageThreadPage');
				$lockOnPage   = (int) $board->getSetting('epheLockThreadPage');
				$deleteOnPage = (int) $board->getSetting('epheDeleteThreadPage');
				
				
				// Don't do anything unless we have to.
				if ($sageOnDay || $lockOnDay || $deleteOnDay || $sageOnPage || $lockOnPage || $deleteOnPage)
				{
					$this->comment("       Pruning /{$board->board_uri}/...");
					
					// Modify threads based on these settings.
					foreach ($board->threads as $threadIndex => $thread)
					{
						$threadPage = (int) ( floor($threadIndex / $threadsPerPage) + 1 );
						$modified   = false;
						$replyLast  = clone $thread->reply_last;
						
						// x on day since last reply
						// This is asking if:
						// 1) The setting is set ($x > 0)
						// 2) the last reply date + the number of days permitted by each setting is < now.
						
						if (!$thread->isBumplocked() && (
							($sageOnDay > 0  && $replyLast->addDays($sageOnDay)->isPast()) ||
							($sageOnPage > 0 && $sageOnPage <= $threadPage)
						))
						{
							$this->comment("           Bumplocking #{$thread->board_id}");
							$modified = true;
							$thread->bumplocked_at = $carbonNow;
						}
						
						if (!$thread->isLocked() && (
							($lockOnDay > 0  && $replyLast->addDays($lockOnDay)->isPast()) ||
							($lockOnPage > 0 && $lockOnPage <= $threadPage)
						))
						{
							$this->comment("           Locking #{$thread->board_id}");
							$modified = true;
							$thread->locked_at = $carbonNow;
						}
						
						if (!$thread->isDeleted() && (
							($deleteOnDay > 0  && $replyLast->addDays($sageOnDay)->isPast()) ||
							($deleteOnPage > 0 && $deleteOnPage <= $threadPage)
						))
						{
							$this->comment("           Deleting #{$thread->board_id}");
							$modified = true;
							$thread->deleted_at = $carbonNow;
						}
						
						if ($modified)
						{
							$threadsToSave[] = $thread;
						}
					}
					
					if (count($threadsToSave))
					{
						// Save all at once.
						$board->threads()->saveMany($threadsToSave);
					}
					else
					{
						$this->comment("           Nothing to do.");
					}
				}
			}
			
		});
	}
	
}
