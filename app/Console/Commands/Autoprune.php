<?php namespace App\Console\Commands;

use App\Board;
use App\FileStorage;
use App\Post;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Settings;

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
				// Avoid selecting the content of a thread.
				$query->select(
					'post_id',
					'reply_to',
					'board_id',
					'reply_to_board_id',
					'reply_last',
					'bumped_last',
					'created_at',
					'updated_at',
					'deleted_at',
					'stickied_at',
					'bumplocked_at',
					'locked_at',
					'body_parsed_at',
					'author_ip',
					'author_ip_nulled_at'
				);
				
				// OPs
				$query->whereNull('reply_to');
				// Not stickied
				$query->whereNull('stickied_at');
				// Oldest first
				$query->orderBy('bumped_last', 'desc');
			},
		])->chunk(25, function($boards) use ($carbonNow) {
			
			$this->comment("    Pruning 25 boards...");
			
			// With each board, fetch their autoprune settings.
			foreach ($boards as $board)
			{
				$this->handleThreadEphemeral($board, $carbonNow);
			}
			
		});
		
		$this->handlePostInformation();
		
		// Run this second so pruned posts from the above are included.
		$this->handleMediaFiles();
	}
	
	/**
	 * Manage media.
	 *
	 * @return void
	 */
	protected function handleMediaFiles()
	{
		$this->comment("    Pruning media data...");
		
		$mediaOrphanLife = (int) Settings::get('epheMediaPrune', 0);
		
		if ($mediaOrphanLife)
		{
			$carbonLife = Carbon::now()->subDays($mediaOrphanLife);
			
			$files = FileStorage::whereOrphan()
				->where('last_uploaded_at', '<=', $carbonLife)
				->get();
			
			$affected = 0;
			
			foreach ($files as $file)
			{
				if ($file->hasFile())
				{
					++$affected;
					$file->deleteFile();
				}
			}
			
			$this->comment("      Pruned {$affected} file(s).");
		}
	}
	
	/**
	 * Manage post personal information (IPs)
	 *
	 * @return void
	 */
	protected function handlePostInformation()
	{
		$this->comment("    Pruning posts data...");
		
		$postIpLife      = (int) Settings::get('ephePostIpLife', 0);
		$postTrashedLife = (int) Settings::get('ephePostHardDelete', 0);
		
		if ($postIpLife)
		{
			$carbonLife = Carbon::now()->subDays($postIpLife);
			
			$affected = Post::withTrashed()
				// Fetch posts older than the cutoff date.
				->where('created_at', '<=', $carbonLife)
				// With an author_ip
				->whereNotNull('author_ip')
				// And nullify the author_ip.
				->update([
					'author_ip' => null,
				]);
			
			$this->comment("      Pruned {$affected} author IP(s).");
		}
		
		if ($postTrashedLife)
		{
			$carbonLife = Carbon::now()->subDays($postTrashedLife);
			
			// Find old posts.
			$forTrash = Post::onlyTrashed()
				->select('post_id', 'deleted_at')
				->where('deleted_at', '<=', $carbonLife);
			
			// Remove relationships.
			$forTrash->chunk(100, function($posts)
			{
				foreach ($posts as $post)
				{
					$post->attachmentLinks()->delete();
				}
			});
			
			// Destroy old, trashed posts.
			$affected = $forTrash->forceDelete();
			
			$this->comment("      Pruned {$affected} trashed post(s).");
		}
	}
	
	/**
	 * Manage threads that have expired.
	 *
	 * @param  App\Board  $board
	 * @param  Carbon\Carbon  $time  Optional Carbon that will be the xed_at timestamps. Defaults to now.
	 * @return void
	 */
	protected function handleThreadEphemeral(Board $board, Carbon $time = null)
	{
		if (is_null($time))
		{
			$time = Carbon::now();
		}
		
		// Get important settings.
		$threadsPerPage = (int) $board->getConfig('postsPerPage', 10);
		
		// Collect a list of threads which have been modified.
		$threadsToSave  = [];
		
		
		// There are two groups of autoprune settings.
		// x on day since last reply
		$sageOnDay    = (int) $board->getConfig('epheSageThreadDays', false);
		$lockOnDay    = (int) $board->getConfig('epheLockThreadDays', false);
		$deleteOnDay  = (int) $board->getConfig('epheDeleteThreadDays', false);
		
		// x on page (meaning the thread has fallen to this page)
		$sageOnPage   = (int) $board->getConfig('epheSageThreadPage', false);
		$lockOnPage   = (int) $board->getConfig('epheLockThreadPage', false);
		$deleteOnPage = (int) $board->getConfig('epheDeleteThreadPage', false);
		
		
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
					$thread->bumplocked_at = $time;
				}
				
				if (!$thread->isLocked() && (
					($lockOnDay > 0  && $replyLast->addDays($lockOnDay)->isPast()) ||
					($lockOnPage > 0 && $lockOnPage <= $threadPage)
				))
				{
					$this->comment("           Locking #{$thread->board_id}");
					$modified = true;
					$thread->locked_at = $time;
				}
				
				if (!$thread->isDeleted() && (
					($deleteOnDay > 0  && $replyLast->addDays($sageOnDay)->isPast()) ||
					($deleteOnPage > 0 && $deleteOnPage <= $threadPage)
				))
				{
					$this->comment("           Deleting #{$thread->board_id}");
					$modified = true;
					$thread->deleted_at = $time;
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
}
