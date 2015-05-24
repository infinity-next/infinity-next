<?php namespace App\Http\Requests;

use App\Board;
use App\Http\Controllers\Board\BoardController;

use Auth;
use View;

class PostRequest extends Request {
	
	protected $board;
	protected $user;
	
	/**
	 * Returns if the client has access to this form.
	 *
	 * @return Boolean
	 */
	public function authorize()
	{
		return true;
	}
	
	/**
	 * Returns validation rules for this request.
	 *
	 * @return Array (\Validation rules)
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
	
	
	public function getBoard()
	{
		return $this->board;
	}
	
	public function getUser()
	{
		return $this->board;
	}
	
	public function setBoard(Board $board)
	{
		return ($this->board = $board);
	}
	
	public function setUser($user)
	{
		return ($this->user = $user);
	}
	
	
	/*
	// OPTIONAL OVERRIDE
	public function forbiddenResponse()
	{
		// Optionally, send a custom response on authorize failure 
		// (default is to just redirect to initial page with errors)
		// 
		// Can return a response, a view, a redirect, or whatever else
		return Response::make('Permission denied foo!', 403);
	}
	
	*/
	
	// OPTIONAL OVERRIDE
	public function response(array $errors)
	{
		$redirectURL = $this->getRedirectUrl();
		
		return redirect($redirectURL)
			->withInput($this->except($this->dontFlash))
			->withErrors($errors, $this->errorBag);
	}
}