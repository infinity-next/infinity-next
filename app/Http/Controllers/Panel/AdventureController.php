<?php namespace App\Http\Controllers\Panel;

use App\Board;
use App\BoardAdventure;
use App\Http\Controllers\Panel\PanelController;
use App\Support\IP;

use Request;

class AdventureController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Adventure Controller
	|--------------------------------------------------------------------------
	|
	| Anon! Grab my hand!
	| ADVENTURE.
	|
	| This controller forwards the user to a random board and creates an
	| adventure token for their IP in the database that belongs to the board.
	| If they post within an hour after landing, their post gets an adventurer
	| meta icon. This only happens once per IP per board.
	|
	*/
	
	const VIEW_ADVENTURE = "panel.adventure";
	
	public function getIndex()
	{
		if (!$this->option('adventureEnabled'))
		{
			return abort(404);
		}
		
		$adventures = BoardAdventure::select('board_uri')
			->where('adventurer_ip', new IP)
			->get();
		
		$board_uris = [];
		
		foreach ($adventures as $adventure)
		{
			$board_uris[] = $adventure->board_uri;
		}
		
		$board = Board::select('board_uri')
			->whereNotIn('board_uri', $adventures)
			->wherePublic()
			->whereIndexed()
			->whereLastPost(48)
			->get();
		
		if (count($board))
		{
			$board = $board->random(1);
			
			$newAdventure = new BoardAdventure([
				'board_uri'     => $board->board_uri,
				'adventurer_ip' => new IP,
			]);
			$newAdventure->expires_at = $newAdventure->freshTimestamp()->addHours(1);
			$newAdventure->save();
		}
		else
		{
			$board = false;
		}
		
		return $this->view(static::VIEW_ADVENTURE, [
			'board'     => $board,
		]);
	}
	
}