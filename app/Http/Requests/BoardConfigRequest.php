<?php namespace App\Http\Requests;

use App\Board;
use App\OptionGroup;
use App\Services\UserManager;

use Illuminate\Routing\Controller;

use Auth;
use View;

class BoardConfigRequest extends Request {
	
	/**
	 * A list of applicable board option groups.
	 *
	 * @var App\OptionGroup
	 */
	protected $boardOptionGroups;
	
	/**
	 * Fetches the user and our board config.
	 *
	 * @return void
	 */
	public function __construct(UserManager $manager, Board $board)
	{
		$this->user = $manager->user;
	}
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
		foreach ($this->getBoardOptions() as $optionGroup)
		{
			foreach ($optionGroup->options as $option)
			{
				if (isset($input[$option->option_name]))
				{
					$input[$option->option_name] = $option->getSanitaryInput($input[$option->option_name]);
				}
			}
		}
		
		return $input;
	}
	
	/**
	 * Supplies validation rules for this request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$rules = [];
		
		foreach ($this->getBoardOptions() as $optionGroup)
		{
			foreach ($optionGroup->options as $option)
			{
				$rules[$option->option_name] = $option->getValidation();
			}
		}
		
		return $rules;
	}
	
	/**
	 * Determines if the client has access to this form.
	 *
	 * @return boolean
	 */
	public function authorize()
	{
		return $this->user->can('board.config', $this->board);
	}
	
	/**
	 *
	 *
	 * @return array
	 */
	public function getBoardOptions()
	{
		if (!isset($this->boardOptionGroups))
		{
			$this->boardOptionGroups = OptionGroup::getBoardConfig($this->board);
		}
		
		return $this->boardOptionGroups;
	}
	
}