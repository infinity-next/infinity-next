<?php namespace App\Http\Requests;

use App\Ban;
use App\Board;
use App\FileStorage;
use App\Post;
use App\PostChecksum;
use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Services\UserManager;
use App\Support\IP;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Auth;
use Cache;
use Validator;
use View;

class PostRequest extends Request implements ApiContract {
	
	use ApiController;
	
	const VIEW_BANNED = "errors.banned";
	
	/**
	 * Input items that should not be returned when reloading the page.
	 *
	 * @var array
	 */
	protected $dontFlash = ['password', 'password_confirmation', 'captcha'];
	
	/**
	 * The board pertinent to the request.
	 *
	 * @var App\Board
	 */
	protected $board;
	
	/**
	 * A ban pulled during validation checks.
	 *
	 * @var App\Ban
	 */
	protected $ban;
	
	/**
	 * Indicates if we're checking a traditional file post or a dropzone file array.
	 *
	 * @var boolean
	 */
	protected $dropzone;
	
	/**
	 * The thread we're replying to.
	 *
	 * @var App\Post
	 */
	protected $thread;
	
	/**
	 * The user.
	 *
	 * @var App\Trait\PermissionUser
	 */
	protected $user;
	
	/**
	 * Does this post respect the robot?
	 * If set to false during validation and failedValidation is triggered,
	 * an automatic board ban will be issued by The Robot for a variable length.
	 *
	 * @var boolean
	 */
	protected $respectTheRobot = true;
	
	/**
	 * Fetches the user and our board config.
	 *
	 * @return void
	 */
	public function __construct(Board $board, Post $thread, UserManager $manager)
	{
		$this->board  = $board;
		$this->user   = $manager->user;
		
		if ($thread->exists)
		{
			$this->thread = $thread;
		}
		else
		{
			$this->thread = false;
		}
	}
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
		$this->dropzone = isset($input['dropzone']);
		
		if (isset($input['files']) && is_array($input['files']))
		{
			// Having an [null] file array passes validation.
			$input['files'] = array_filter($input['files']);
		}
		
		if (isset($input['capcode']) && $input['capcode'])
		{
			$user = $this->user;
			
			if ($user && !$user->isAnonymous())
			{
				$role = $user->roles->where('role_id', (int) $input['capcode'])->first();
				
				if ($role && $role->capcode != "")
				{
					$input['capcode_id'] = (int) $role->role_id;
					// $input['author']     = $user->username;
				}
				else
				{
					$this->failedAuthorization();
				}
			}
			else
			{
				unset($input['capcode']);
			}
		}
		
		if (!$this->board->canPostWithAuthor($this->user, !!$this->thread))
		{
			unset($input['author']);
		}
		
		if (!$this->board->canPostWithSubject($this->user, !!$this->thread))
		{
			unset($input['subject']);
		}
		
		return $input;
	}
	
	/**
	 * Returns if the client has access to this form.
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		// Ban check.
		$ban = Ban::getBan($this->ip(), $this->board->board_uri);
		
		if ($ban)
		{
			$this->ban = $ban;
			return false;
		}
		
		// Locked thread check.
		if ($this->thread instanceof Post && $this->thread->isLocked() && !$this->user->canPostInLockedThreads($this->board))
		{
			return false;
		}
		
		## TODO ##
		// Separate these permsisions.
		return $this->user->canPostThread() || $this->user->canPostReply();
	}
	
	/**
	 * Returns the response if authorize() fails.
	 *
	 * @return Response
	 */
	public function forbiddenResponse()
	{
		if ($this->ban)
		{
			$url = $this->ban->getRedirectUrl();
			
			if ($this->ajax() || $this->wantsJson())
			{
				return $this->apiResponse([ 'redirect' => $url ]);
			}
			else
			{
				return redirect($url);
			}
		}
		
		return abort(403);
	}
	
	/**
	 * Form specific error messages.
	 *
	 * @return array  Of field to language relationships.
	 */
	public function messages()
	{
		$board = $this->board;
		$postNewLines = (int) $board->getConfig('postNewLines', 0);
		
		return [
			'body.regex' => trans_choice('validation.form.post.body.newlines', $postNewLines, [ 'count' => $postNewLines ]),
		];
	}
	
	/**
	 * Get the proper failed validation response for the request.
	 *
	 * @param  array  $errors
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function response(array $errors)
	{
		if (!$this->respectTheRobot)
		{
			$this->ban = Ban::addRobotBan($this->board);
			return $this->forbiddenResponse();
		}
		
		$redirectURL = $this->getRedirectUrl();
		
		if ($this->wantsJson())
		{
			return $this->apiResponse([ 'errors' => $errors ]);
		}
		
		return redirect($redirectURL)
			->withInput($this->except($this->dontFlash))
			->withErrors($errors, $this->errorBag);
	}
	
	/**
	 * Returns validation rules for this request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$board = $this->board;
		$user  = $this->user;
		$rules = [
			'author'  => [
				"string",
				"encoding:UTF-8",
			],
			
			'email'   => [
				"string",
				"encoding:UTF-8",
			],
			
			'subject' => [
				"string",
				"encoding:UTF-8",
			],
		];
		
		if (!$this->thread && $board->getConfig('threadRequireSubject'))
		{
			$rules['subject'][] = "required";
		}
		
		// Modify the validation rules based on what we've been supplied.
		if ($board && $user)
		{
			$rules['body'] = [
				"encoding:UTF-8",
				"min:" . $board->getConfig('postMinLength', 0),
				"max:" . $board->getConfig('postMaxLength', 65534),
			];
			
			$newLineMax = (int) $board->getConfig('postNewLines', 0);
			
			if ($newLineMax > 0)
			{
				$rules['body'][] = "regex:/^(\n?(.*)){1,{$newLineMax}}$/";
			}
			
			if (!$board->canAttach($user))
			{
				$rules['body'][]  = "required";
				$rules['files'][] = "array";
				$rules['files'][] = "max:0";
			}
			else
			{
				$rules['body'][]  = "required_without:files";
				
				$attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));
				
				// There are different rules for starting threads.
				if (!($this->thread instanceof Post))
				{
					$attachmentsMin = max(0, (int) $board->getConfig('postAttachmentsMin', 0), (int) $board->getConfig('threadAttachmentsMin', 0));
				}
				else
				{
					$attachmentsMin = max(0, (int) $board->getConfig('postAttachmentsMin', 0));
				}
				
				
				// Add the rules for file uploads.
				if ($this->dropzone)
				{
					$fileToken = "files.hash";
					
					// JavaScript enabled hash posting
					static::rulesForFileHashes($board, $rules);
					
					if ($attachmentsMin > 0)
					{
						$rules['files.name'][] = "required";
						$rules['files.name'][] = "min:{$attachmentsMin}";
						$rules['files.hash'][] = "required";
						$rules['files.hash'][] = "min:{$attachmentsMin}";
					}
				}
				else
				{
					$fileToken = "files";
					
					// Vanilla HTML upload
					static::rulesForFiles($board, $rules);
					
					if ($attachmentsMin > 0)
					{
						$rules['files'][] = "required";
						$rules['files'][] = "min:{$attachmentsMin}";
					}
				}
				
				for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment)
				{
					// Can only attach existing files.
					if (!$user->canAttachNew($board) && $user->canAttachOld($board))
					{
						$rules["{$fileToken}.{$attachment}"][] = "file_old";
					}
					// Can only attach new files.
					else if ($user->canAttachNew($board) && !$user->canAttachOld($board))
					{
						$rules["{$fileToken}.{$attachment}"][] = "file_new";
					}
					
					for ($otherAttachment = 0; $otherAttachment < $attachment; ++$otherAttachment)
					{
						$rules["{$fileToken}.{$attachment}"][] = "different:{$fileToken}.{$otherAttachment}";
					}
				}
			}
		}
		
		return $rules;
	}
	
	/**
	 * Returns rules specifically for files for a board.
	 *
	 * @param  Board  $board
	 * @param  array  $rules (POINTER, MODIFIED)
	 * @return array  Validation rules.
	 */
	public static function rulesForFiles(Board $board, array &$rules)
	{
		global $app;
		
		$attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));
		
		$rules['spoilers'] = "boolean";
		
		$rules['files'][] = "array";
		$rules['files'][] = "max:{$attachmentsMax}";
		
		// Create an additional rule for each possible file.
		for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment)
		{
			$rules["files.{$attachment}"] = [
				"between:0," . ( (int) $app['settings']('attachmentFilesize') * 1.024),
				"file_integrity",
			];
		}
	}
	
	/**
	 * Returns rules specifically for dropzone files for a board.
	 *
	 * @param  Board  $board
	 * @param  array  $rules (POINTER, MODIFIED)
	 * @return array  Validation rules.
	 */
	public static function rulesForFileHashes(Board $board, array &$rules)
	{
		global $app;
		
		$attachmentsMax = max(0, (int) $board->getConfig('postAttachmentsMax', 1));
		
		$rules['files'][] = "array";
		$rules['files'][] = "between:2,3"; // [files.hash,files.name] or +[files.spoiler]
		
		$rules['files.name']    = ["array", "max:{$attachmentsMax}"];
		$rules['files.hash']    = ["array", "max:{$attachmentsMax}"];
		$rules['files.spoiler'] = ["array", "max:{$attachmentsMax}"];
		
		// Create an additional rule for each possible file.
		for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment)
		{
			$rules["files.name.{$attachment}"] = [
				"string",
				"required_with:files.hash.{$attachment}",
				"between:1,254",
				"file_name",
			];
			
			$rules["files.hash.{$attachment}"] = [
				"string",
				"required_with:files.name.{$attachment}",
				"md5",
				"exists:files,hash,banned,0",
			];
			
			$rules["files.spoiler.{$attachment}"] = [
				"boolean",
			];
		}
	}
	
	/**
	 * Validate the class instance.
	 * This overrides the default invocation to provide additional rules after the controller is setup.
	 *
	 * @return void
	 */
	public function validate()
	{
		$board     = $this->board;
		$thread    = $this->thread;
		$user      = $this->user;
		
		$ip        = new IP($this->ip());
		$carbon    = new \Carbon\Carbon;
		
		$validator = $this->getValidatorInstance();
		$messages  = $validator->errors();
		$isReply   = $this->thread instanceof Post;
		
		if ($isReply)
		{
			$floodTime = site_setting('postFloodTime');
			
			// Check global flood.
			$nextPostTime = Carbon::createFromTimestamp(Cache::get('last_post_for_' . $ip->toLong(), 0) + $floodTime);
			
			if ($nextPostTime->isFuture())
			{
				$timeDiff = $nextPostTime->diffInSeconds() + 1;
				
				$messages->add("flood", trans_choice("validation.custom.post_flood", $timeDiff, [
						'time_left' => $timeDiff,
				]));
				
				$this->failedValidation($validator);
				return;
			}
		}
		else
		{
			$floodTime = site_setting('threadFloodTime');
			
			// Check global flood.
			$nextPostTime = Carbon::createFromTimestamp(Cache::get('last_thread_for_' . $ip->toLong(), 0) + $floodTime);
			
			if ($nextPostTime->isFuture())
			{
				$timeDiff = $nextPostTime->diffInSeconds() + 1;
				
				$messages->add("flood", trans_choice("validation.custom.thread_flood", $timeDiff, [
						'time_left' => $timeDiff,
				]));
				
				$this->failedValidation($validator);
				return;
			}
		}
		
		// Board-level setting validaiton.
		$validator->sometimes('captcha', "required|captcha", function($input) use ($board) {
			return !$board->canPostWithoutCaptcha($this->user);
		});
		
		if (!$validator->passes())
		{
			$this->failedValidation($validator);
		}
		else
		{
			if (!$this->user->canAdminConfig() && $board->canPostWithoutCaptcha($this->user))
			{
				// Check last post time for flood.
				$floodTime = site_setting('postFloodTime');
				
				if ($floodTime > 0)
				{
					$lastPost = Post::getLastPostForIP();
					
					if ($lastPost)
					{
						$floodTimer = clone $lastPost->created_at;
						$floodTimer->addSeconds($floodTime);
						
						if ($floodTimer->isFuture())
						{
							$messages->add("flood", trans("validation.custom.post_flood", [
								'time_left' => $floodTimer->diffInSeconds(),
							]));
							
							$this->failedValidation($validator);
							return;
						}
					}
				}
			}
			
			
			// Validate individual files being uploaded right now.
			$this->validateOriginality();
		}
		
		if (count($validator->errors()))
		{
			$this->failedValidation($validator);
		}
		else if (!$this->passesAuthorization())
		{
			$this->failedAuthorization();
		}
		
	}
	
	protected function validateOriginality()
	{
		$board     = $this->board;
		$thread    = $this->thread;
		$user      = $this->user;
		$input     = $this->all();
		
		$validated = true;
		$validator = $this->getValidatorInstance();
		$messages  = $validator->errors();
		
		// Process uploads.
		if (isset($input['files']))
		{
			$uploads = $input['files'];
			
			if(count($uploads) > 0)
			{
				
				// Standard upload originality and integrity checks.
				if (!$this->dropzone)
				{
					foreach ($uploads as $uploadIndex => $upload)
					{
						// If a file is uploaded that has a specific filename, it breaks the process.
						if(method_exists($upload, "getPathname") && !file_exists($upload->getPathname()))
						{
							$validated = false;
							$messages->add("files.{$uploadIndex}", trans("validation.custom.file_corrupt", [
								"filename" => $upload->getClientOriginalName(),
							]));
						}
					}
				}
				
				if ($board->getConfig('originalityImages'))
				{
					foreach ($uploads as $uploadIndex => $upload)
					{
						if (!($upload instanceof UploadedFile))
						{
							continue;
						}
						
						if ($board->getConfig('originalityImages') == "thread")
						{
							if ($thread instanceof Post && $originalPost = FileStorage::checkUploadExists($upload, $board, $thread))
							{
								$validated = false;
								$messages->add("files.{$uploadIndex}", trans("validation.custom.unoriginal_image_thread", [
									"filename" => $upload->getClientOriginalName(),
									"url"      => $originalPost->getURL(),
								]));
							}
						}
						else if ($originalPost = FileStorage::checkUploadExists($upload, $board))
						{
							$validated = false;
							$messages->add("files.{$uploadIndex}", trans("validation.custom.unoriginal_image_board", [
								"filename" => $upload->getClientOriginalName(),
								"url"      => $originalPost->getURL(),
							]));
						}
					}
				}
			}
			// Dropzone hash checks.
			else if ($board->getConfig('originalityImages'))
			{
				$uploadsToCheck = false;
				
				if (isset($uploads['hash']))
				{
					$hashes = true;
					$uploadsToCheck = $uploads['hash'];
				}
				else if (isset($uploads['files']))
				{
					$hashes = false;
					$uploadsToCheck = $input['files']
				}
				
				foreach ($uploadsToCheck as $uploadIndex => $upload)
				{
					
					
					if ($board->getConfig('originalityImages') == "thread")
					{
						if ($thread instanceof Post && $originalPost = FileStorage::checkHashExists($hash, $board, $thread))
						{
							$validated = false;
							$messages->add("files.{$uploadIndex}", trans("validation.custom.unoriginal_image_thread", [
								"filename" => $uploads['name'][$uploadIndex],
								"url"      => $originalPost->getURL(),
							]));
						}
					}
					else if ($originalPost = FileStorage::checkHashExists($upload, $board))
					{
						$validated = false;
						$messages->add("files.{$uploadIndex}", trans("validation.custom.unoriginal_image_board", [
							"filename" => $uploads['name'][$uploadIndex],
							"url"      => $originalPost->getURL(),
						]));
					}
				}
			}
		}
		
		
		// Process body checksum for origianlity.
		$strictness = $board->getConfig('originalityPosts');
		
		if (isset($input['body']) && $strictness)
		{
			$checksum = Post::makeChecksum($input['body']);
			
			if ($strictness == "board" || $strictness == "boardr9k")
			{
				$checksums = PostChecksum::getChecksum($checksum, $board);
			}
			else if ($strictness == "site" || $strictness == "siter9k")
			{
				$checksums = PostChecksum::getChecksum($checksum);
			}
			
			//dd($checksums);
			
			if ($checksums->count())
			{
				$validated = false;
				
				$messages->add("body", trans("validation.custom.unoriginal_content"));
				
				// If we are in R9K mode, set $respectTheRobot property to to false.
				// This will trigger a Robot ban in failedValidation.
				$this->respectTheRobot = !($strictness == "boardr9k" || $strictness == "siter9k");
			}
		}
		
		if ($validated !== true)
		{
			$this->failedValidation($validator);
			return;
		}
	}
	
}
