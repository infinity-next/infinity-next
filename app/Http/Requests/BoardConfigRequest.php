<?php namespace App\Http\Requests;

use App\Board;
use App\OptionGroup;

use App\Http\Requests\RequestAcceptsUserAndBoard;

use Auth;
use View;

class BoardConfigRequest extends Request {
	
	use RequestAcceptsUserAndBoard;
	
	/**
	 * A list of applicable board option groups.
	 *
	 * @var App\OptionGroup
	 */
	protected $boardOptionGroups;
	
	/**
	 * Get all form input.
	 *
	 * @return array
	 */
	public function all()
	{
		$input = parent::all();
		
		if ($optionGroups = $this->getBoardOptionGroups())
		{
			foreach ($optionGroups as $optionGroup)
			{
				foreach ($optionGroup->options as $option)
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
		
		if ($optionGroups = $this->getBoardOptionGroups())
		{
			foreach ($optionGroups as $optionGroup)
			{
				foreach ($optionGroup->options as $option)
				{
					$rules[$option->option_name] = $option->getValidation();
				}
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
		if (($user = $this->getUser()) && ($board = $this->getBoard()))
		{
			return $user->can('board.config', $board);
		}
		
		return true;
	}
	
	
	/**
	 * Sets the request's board and fetches option groups.
	 *
	 * @param  Board  $board
	 * @return void
	 */
	public function setBoard(Board $board)
	{
		$this->board             = $board;
		$this->boardOptionGroups = OptionGroup::getBoardConfig($board);
	}
	
	/**
	 * Gets the board options as set in setBoard.
	 *
	 * @return array
	 */
	public function getBoardOptionGroups()
	{
		return $this->boardOptionGroups;
	}
	
}