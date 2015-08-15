<?php namespace App\Http\Controllers\Panel\Boards;

use App\Board;
use App\Report;
use App\Http\Controllers\Panel\PanelController;

class ReportsController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Reprts Controller
	|--------------------------------------------------------------------------
	|
	| The reports controller will display and allow the handling of posts reports,
	| either on all boards (for global moderators), or local boards.
	|
	*/
	
	public function getIndex()
	{
		return "You can't hide from the wolf, growl.";
	}
}