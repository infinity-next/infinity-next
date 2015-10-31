<?php namespace App\Http\Requests;

use App\Ban;
use App\Board;
use App\Post;
use App\Services\UserManager;

use Auth;
use Validator;
use View;

class PostRequest extends Request {
	
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
	}
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
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
				$role = $user->roles->where('role_id', $input['capcode'])->first();
				
				if ($role && $role->capcode != "")
				{
					$input['capcode_id'] = (int) $role->role_id;
					$input['author']     = $user->username;
				}
			}
			else
			{
				unset($input['capcode']);
			}
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
		
		## TODO ##
		// Separate these permsisions.
		return $this->canPostThread() || $this->canPostReply();
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
			
			if ($this->wantsJson())
			{
				return response()->json([ 'redirect' => $url ]);
			}
			else
			{
				return redirect($url);
			}
		}
		
		return abort(403);
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
			// Nothing, by default.
			// Post options are contingent on board settings and user permissions.
		];
		
		// Modify the validation rules based on what we've been supplied.
		if ($board && $user)
		{
			$rules['body'] = [
				"max:" . $board->getConfig('postMaxLength', 65534),
			];
			
			if (!$board->canAttach($user))
			{
				$rules['body'][]  = "required";
				$rules['files'][] = "array";
				$rules['files'][] = "max:0";
			}
			else
			{
				$rules['body'][]  = "required_without:files";
				
				// Add the rules for file uploads.
				if (isset($this->all()['dropzone']))
				{
					$fileToken = "files.hash";
					
					// JavaScript enabled hash posting
					static::rulesForFileHashes($board, $rules);
				}
				else
				{
					$fileToken = "files";
					
					// Vanilla HTML upload
					static::rulesForFiles($board, $rules);
				}
				
				
				$attachmentsMax = $board->getConfig('postAttachmentsMax', 1);
				
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
		
		$attachmentsMax = $board->getConfig('postAttachmentsMax', 1);
		
		$rules['spoilers'] = "boolean";
		
		$rules['files'][] = "array";
		$rules['files'][] = "min:1";
		$rules['files'][] = "max:{$attachmentsMax}";
		
		// Create an additional rule for each possible file.
		for ($attachment = 0; $attachment < $attachmentsMax; ++$attachment)
		{
			$rules["files.{$attachment}"] = [
				//"mimes:jpeg,gif,png,bmp,svg,swf,webm,mp4,ogg,mp3,mpga,mpeg,wav,pdf,epub",
				"between:0," . $app['settings']('attachmentFilesize'),
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
		
		$attachmentsMax = $board->getConfig('postAttachmentsMax', 1);
		
		$rules['files'][] = "array";
		$rules['files'][] = "between:2,3";
		
		$rules['files.name']    = "array|max:{$attachmentsMax}";
		$rules['files.hash']    = "array|max:{$attachmentsMax}";
		$rules['files.spoiler'] = "array|max:{$attachmentsMax}";
		
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
	 * Get the response for a forbidden operation.
	 *
	 * @param  array $errors
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function response(array $errors)
	{
		$redirectURL = $this->getRedirectUrl();
		
		if ($this->wantsJson())
		{
			return response(['errors' => $errors]);
		}
		
		return redirect($redirectURL)
			->withInput($this->except($this->dontFlash))
			->withErrors($errors, $this->errorBag);
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
		$user      = $this->user;
		
		$validator = $this->getValidatorInstance();
		$messages  = $validator->errors();
		$isReply   = $this->thread instanceof Post;
		
		if ($isReply)
		{
			// Check global flood.
			$lastPost = Post::select('created_at')
				->where('author_ip', inet_pton($this->ip()))
				->where('created_at', '>=', \Carbon\Carbon::now()->subSeconds(5))
				->first();
			
			if ($lastPost instanceof Post)
			{
				$timeDiff = (5 - $lastPost->created_at->diffInSeconds()) + 1;
				
				$messages = $validator->errors();
				$messages->add("flood", trans_choice("validation.custom.post_flood", $timeDiff, [
						'time_left' => $timeDiff,
				]));
				$this->failedValidation($validator);
				return;
			}
		}
		else
		{
			// Check global flood.
			$lastThread = Post::select('created_at')
				->where('author_ip', inet_pton($this->ip()))
				->where('created_at', '>=', \Carbon\Carbon::now()->subSeconds(20))
				->op()
				->first();
			
			if ($lastThread instanceof Post)
			{
				$timeDiff = (20 - $lastThread->created_at->diffInSeconds()) + 1;
				
				$messages = $validator->errors();
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
							$messages->add("body", trans("validation.custom.post_flood", [
								'time_left' => $floodTimer->diffInSeconds(),
							]));
						}
					}
				}
			}
			
			
			// Validate individual files.
			
			$input = $this->all();
			
			// Process uploads.
			if (isset($input['files']))
			{
				$uploads = $input['files'];
				
				if(count($uploads) > 0)
				{
					foreach ($uploads as $uploadIndex => $upload)
					{
						// If a file is uploaded that has a specific filename, it breaks the process.
						if(method_exists($upload, "getPathname") && !file_exists($upload->getPathname()))
						{
							$messages->add("files.{$uploadIndex}", trans("validation.custom.file_corrupt", [
								"filename" => $upload->getClientOriginalName(),
							]));
						}
					}
				}
			}
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
	
}
