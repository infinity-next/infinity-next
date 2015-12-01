<?php namespace App\Http\Controllers\API\Legacy;

use App\Board;
use App\Post;

use App\Http\Controllers\Controller as ParentController;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BoardController extends ParentController {
	
	/**
	 * Returns a thread and its replies to the client. 
	 *
	 * @param  Board $board
	 * @param  Post  $thread
	 * @return Response
	 */
	public function getThread(Board $board, Post $thread)
	{
		$posts    = collect([$thread])->merge($thread->replies);
		$response = [ 'posts' => [] ];
		
		foreach ($posts as $post)
		{
			// Actual post information.
			$postArray = [
				'no'             => (int) $post->board_id,
				'resto'          => (int) $post->reply_to_board_id ?: 0,
				
				'sticky'         => (bool) $post->isStickied(),
				'locked'         => (bool) $post->isLocked(),
				'cyclical'       => (bool) $post->isCyclic(),
				
				'name'           => (string) $post->author,
				'sub'            => (string) $post->subject,
				'com'            => (string) $post->getBodyFormatted(),
				
				'time'           => (int) $post->created_at->timestamp,
				'last_modified'  => (int) $post->updated_at->timestamp,
				
				'omitted_posts'  => 0,
				'omitted_images' => 0,
			];
			
			// Attachment information.
			foreach ($post->attachments as $attachmentIndex => $attachment)
			{
				$attachmentArray = [
					'filename'   => $attachment->getBaseFileName(),
					'ext'        => "." . $attachment->getExtension(),
					'tim'        => $attachment->getFileName("%t-%i"),
					'md5'        => base64_encode(hex2bin($attachment->hash)),
					
					'fsize'      => 0,
					
					'tn_h'       => 250,
					'tn_w'       => 250,
					'h'          => 250,
					'w'          => 250,
				];
				
				if ($attachmentIndex === 0)
				{
					$postArray = array_merge($postArray, $attachmentArray);
				}
				else
				{
					if (!isset($postArray['extra_files']))
					{
						$postArray['extra_files'] = [];
					}
					
					$postArray['extra_files'][] = $attachmentArray;
				}
			}
			
			$response['posts'][] = $postArray;
		}
		
		return response()->json($response);
	}
	
}
