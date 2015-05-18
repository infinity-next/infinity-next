<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\FileStorage;
use App\FileAttachment;
use App\Post;
use App\Http\Controllers\MainController;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;

use Input;
use File;
use Storage;
use Response;
use Validator;
use View;

class BoardController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| Board Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles any requests that point to a directory which is
	| otherwise unavailable. It will determine if such a board exists and then
	| distribute content based on what what additional directries are specified
	| and what information is available to the accessing user.
	|
	*/
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads, depending on the optional page
	 * parameter, which determines the thread offset.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request, Board $board, $page = 1)
	{
		// Determine what page we are on.
		$pages = $board->getPageCount();
		
		// Clamp the page to real values.
		if ($page <= 0)
		{
			$page = 1;
		}
		elseif ($page > $pages)
		{
			$page = $pages;
		}
		
		// Determine if we have a next/prev button.
		$pagePrev = ($page > 1) ? $page - 1 : false;
		$pageNext = ($page < $pages) ? $page + 1 : false;
		
		// Load our list of threads and their latest replies.
		$posts = $board->getThreadsForIndex($page);
		
		return View::make('board', [
			'board'    => $board,
			'posts'    => $posts,
			'reply_to' => false,
			
			'pages'    => $pages,
			'page'     => $page,
			'pagePrev' => $pagePrev,
			'pageNext' => $pageNext,
		] );
	}
	
	/**
	 * Renders a thread.
	 *
	 * @return Response
	 */
	public function getThread(Request $request, Board $board, $thread = NULL)
	{
		if (is_null($thread))
		{
			return redirect($board->board_uri);
		}
		
		// Pull the thread.
		$thread = $board->getThread($thread);
		
		if ($thread->reply_to)
		{
			return redirect("{$board->board_uri}/thread/{$thread->op->board_id}");
		}
		
		return View::make('board', [
			'board'    => $board,
			'posts'    => [ $thread ],
			'reply_to' => $thread->board_id,
		]);
	}
	
	/**
	 * Handles the creation of a new thread or reply.
	 *
	 * @return Response (redirects to the thread view)
	 */
	public function putThread(Request $request, Board $board, $thread_id = false)
	{
		if ($input = Input::all())
		{
			$canAttach = $board->canAttach($this->user);
			
			if (!$canAttach)
			{
				$requirements = [
					'body'    => 'required|max:' . $board->getSetting('postMaxLength'),
					'captcha' => 'required|captcha',
				];
			}
			else
			{
				$requirements = [
					'body'    => 'required_without:files|max:' . $board->getSetting('postMaxLength'),
					'files'   => 'array',
					'captcha' => 'required|captcha',
				];
			}
			
			$validator = Validator::make($input, $requirements);
			
			
			$post = new Post($input);
			$post->author_ip = $request->ip();
			
			if ($thread_id !== false)
			{
				$thread = $board->getLocalThread($thread_id);
				$post->reply_to = $thread->post_id;
			}
			
			if (isset($input['capcode']) && $input['capcode'])
			{
				if (!$this->user->isAnonymous())
				{
					$role = $this->user->roles->where('role_id', $input['capcode'])->first();
					
					if ($role && $role->capcode != "")
					{
						$post->capcode_id = (int) $role->role_id;
						$post->author     = $this->user->username;
					}
					else
					{
						return abort(403);
					}
				}
				else
				{
					return abort(403);
				}
			}
			
			
			// Store attachments
			$uploads = array_filter(Input::file('files'));
			
			if ($canAttach && count($uploads) > 0)
			{
				$uploadsSuccessful = true;
				$uploadStorage = [];
				
				foreach ($uploads as $uploadIndex => $upload)
				{
					$fileValidator = Validator::make([
						'file' => $upload,
					], [
						'file' => 'required|mimes:jpeg,gif,png|between:1,5120'
					]);
					
					if ($fileValidator->passes())
					{
						$fileMD5      = md5(File::get($upload));
						$fileTime     = $post->freshTimestamp();
						$storage      = FileStorage::getHash($fileMD5);
						
						if (!($storage instanceof FileStorage))
						{
							$storage = new FileStorage();
							$storage->hash     = $fileMD5;
							$storage->banned   = false;
							$storage->filesize = $upload->getSize();
							$storage->mime     = $upload->getClientMimeType();
							$storage->first_uploaded_at = $fileTime;
							$storage->upload_count = 0;
						}
						
						$storage->last_uploaded_at = $fileTime;
						$storage->upload_count += 1;
						$storage->save();
						
						if (!$storage->banned)
						{
							$uploadStorage[ $uploadIndex ] = $storage;
						}
						else
						{
							$uploadsSuccessful = false;
							$validator->errors()->add('files', "The image \"" . $upload->getClientOriginalName() . "\" is banned from being uploaded.");
						}
					}
					else
					{
						$uploadsSuccessful = false;
						foreach ($fileValidator->errors()->all() as $fileFieldError)
						{
							$validator->errors()->add('files', $fileFieldError);
						}
					}
				}
				
				if ($uploadsSuccessful)
				{
					$board->threads()->save($post);
					
					foreach ($uploads as $uploadIndex => $upload)
					{
						$fileContent = File::get($upload);
						$storage     = $uploadStorage[ $uploadIndex ];
						
						$attachment = new FileAttachment();
						$attachment->post_id  = $post->post_id;
						$attachment->file_id  = $storage->file_id;
						$attachment->filename = $upload->getClientOriginalName() . '.' . $upload->guessExtension();
						$attachment->save();
						
						if (!Storage::exists($storage->getPath()))
						{
							Storage::put($storage->getPath(), $fileContent);
							Storage::makeDirectory($storage->getDirectoryThumb());
							
							$imageManager = new ImageManager;
							$imageManager
								->make($storage->getFullPath())
								->resize(255, 255, function($constraint) {
									$constraint->aspectRatio();
									$constraint->upsize();
								})
								->save($storage->getFullPathThumb());
						}
					}
				}
			}
			else
			{
				$board->threads()->save($post);
			}
			
			if ($thread_id === false)
			{
				return redirect("{$board->board_uri}/thread/{$post->board_id}")->withErrors($validator->errors());
			}
			else
			{
				return redirect("{$board->board_uri}/thread/{$thread->board_id}#{$post->board_id}")->withErrors($validator->errors());
			}
		}
		
		return redirect($board->board_uri);
	}
}
