<?php namespace App\Http\Requests;

use App\Board;
use App\Http\Controllers\Board\BoardController;

use Auth;
use View;

class PostRequest extends Request {
	
	/**
	 * Current Board set by controller.
	 *
	 * @var Board
	 */
	protected $board;
	
	/**
	 * Current Board set by controller.
	 *
	 * @var User|Support\Anonymous
	 */
	protected $user;
	
	/**
	 * Input items that should not be returned when reloading the page.
	 *
	 * @var array
	 */
	protected $dontFlash = ['password', 'password_confirmation', 'captcha'];
	
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
			$user = $this->getUser();
			
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
		return true;
	}
	
	/**
	 * Returns validation rules for this request.
	 *
	 * @param BoardController $controller
	 * @return array
	 */
	public function rules(BoardController $controller)
	{
		$board = $this->getBoard();
		$user  = $this->getUser();
		$rules = [
			// Nothing, by default.
			// Post options are contingent on board settings and user permissions.
		];
		
		// Modify the validation rules based on what we've been supplied.
		if ($board && $user)
		{
			$rules = [
				'body' => "max:" . $board->getSetting('postMaxLength'),
			];
			
			if (!$board->canAttach($this->user))
			{
				$rules['body'] .= "|required";
				$rules['files'] = "array|max:0";
			}
			else
			{
				$rules['body'] .= "|required_without:files";
				$rules['files'] = "array|min:1|max:" . $board->getSetting('attachmentsMax');
				
				// Create an additional rule for each possible file.
				for ($attachment = 0; $attachment < $board->getSetting('attachmentsMax'); ++$attachment)
				{
					$rules["files.{$attachment}"] = "mimes:jpeg,gif,png|between:0," . $controller->option('attachmentFilesize');
				}
			}
		}
		
		return $rules;
	}
	
	/**
	 * Get the response for a forbidden operation.
	 *
	 * @param array $errors
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function response(array $errors)
	{
		$redirectURL = $this->getRedirectUrl();
		
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
		$board = $this->getBoard();
		$user  = $this->getUser();
		
		if (!$board || !$user)
		{
			return parent::validate();
		}
		
		$validator = $this->getValidatorInstance();
		
		$validator->sometimes('captcha', "required|captcha", function($input) use ($board) {
			return !$board->canPostWithoutCaptcha($this->user);
		});
		
		if (!$validator->passes())
		{
			$this->failedValidation($validator);
		}
	}
	
	/**
	 * Returns the request's current board.
	 *
	 * @return Board
	 */
	public function getBoard()
	{
		return $this->board;
	}
	
	/**
	 * Returns the request's current user.
	 *
	 * @return User|Support\Anonymous
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * Sets the request's board.
	 *
	 * @return void
	 */
	public function setBoard(Board $board)
	{
		$this->board = $board;
	}
	
	/**
	 * Returns the request's user.
	 *
	 * @return void
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}
}