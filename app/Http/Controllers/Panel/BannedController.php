<?php namespace App\Http\Controllers\Panel;

use App\Ban;
use App\Board;
use App\Http\Controllers\Panel\PanelController;

use Request;

class BannedController extends PanelController {
	
	/*
	|--------------------------------------------------------------------------
	| Banned Controller
	|--------------------------------------------------------------------------
	|
	| This controller will list active bans applied to your IP addresses and
	| allow you to appeal them and review the status of your appeal.
	|
	*/
	
	const VIEW_BANNED = "panel.banned";
	const VIEW_BAN    = "panel.ban";
	
	/**
	 * View path for the secondary (sidebar) navigation.
	 *
	 * @var string
	 */
	public static $navSecondary = "nav.panel.home";
	
	
	public function getIndex(Board $board)
	{
		$bans = Ban::getBans(Request::ip(), false);
		
		return $this->view(static::VIEW_BANNED, [
			'bans' => $bans,
		]);
	}
	
	public function getBan(Board $board, Ban $ban)
	{
		if (!$ban->canView($this->user))
		{
			return abort(403);
		}
		
		$ban->setRelation('board', $board);
		
		return $this->view(static::VIEW_BAN, [
			'board' => $board,
			'ban'   => $ban,
		]);
	}
	
}