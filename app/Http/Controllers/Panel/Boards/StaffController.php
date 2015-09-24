<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;

use App\Http\Controllers\Panel\PanelController;

use Input;
use Request;
use Validator;

class StaffController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Staff Controller
	|--------------------------------------------------------------------------
	|
	| This is the board staff controller, available only to the board owner and admins.
	| Its only job is to list, remove, update, and add staff members to a board.
	|
	*/
	
	const VIEW_LIST  = "panel.board.staff";
	const VIEW_ADD   = "panel.board.staff.create";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.board";
	
	/**
	 * View path for the tertiary (inner) navigation.
	 *
	 * @var string
	 */
	public static $navTertiary = "nav.panel.board.settings";
	
	
	/**
	 * List all staff members to the user.
	 *
	 * @return Response
	 */
	public function getIndex(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_LIST, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
			
			'tab'    => "staff",
		]);
	}
	
	/**
	 * Opens staff creation form.
	 *
	 * @return Response
	 */
	public function getAdd(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$roles = $board->roles;
		$staff = $board->getStaff();
		
		return $this->view(static::VIEW_ADD, [
			'board'  => $board,
			'roles'  => $roles,
			'staff'  => $staff,
			
			'tab'    => "staff",
		]);
	}
	
	/**
	 * Adds new staff.
	 *
	 * @return Response
	 */
	public function putAdd(Board $board)
	{
		if (!$board->canEditConfig($this->user))
		{
			return abort(403);
		}
		
		$rules     = [];
		$existing  = Input::get('staff-source') == "existing";
		
		if ($existing)
		{
			$rules = [
				'existinguser' => [
					"required",
					"string",
					"exists:users,username",
				],
				'captcha'     => [
					"required",
					"captcha"
				]
			];
			
			$input     = Input::only('existinguser');
			$validator = Validator::make($input, $rules);
		}
		else
		{
			$validator = $this->registrationValidator();
		}
		
		if ($validator->fails())
		{
			return redirect()
				->back()
				->withInput()
				->withErrors($validator->errors());
		}
		
		return ":)";
	}
	
}