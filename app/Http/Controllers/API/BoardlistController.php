<?php namespace App\Http\Controllers\API;

use App\Board;

use App\Contracts\ApiController;
use App\Http\Controllers\BoardlistController as ParentController;
use Input;
use Request;

class BoardlistController extends ParentController implements ApiController {
	
	/**
	 * Show board list to the user, either rendering the full blade template or just the json..
	 *
	 * @return Response|JSON
	 */
	public function getIndex()
	{
		return $this->boardListJson();
	}
	
	/**
	 * Returns basic board details. 
	 *
	 * @return Response
	 */
	public function getDetails(Request $request)
	{
		$boardUris = Input::get('boards', []);
		$boards    = Board::select('board_uri', 'title')
			->whereIn('board_uri', $boardUris)
			->orderBy('board_uri', 'desc')
			->take(20)
			->get();
		
		return $boards;
	}
	
}