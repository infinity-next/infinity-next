<?php

namespace App\Http\Controllers\Panel;

use App\Board;
use App\BoardAdventure;
use App\Support\IP;

/**
 * Jumps the user to a random board based on recent activity.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class AdventureController extends PanelController
{
    const VIEW_ADVENTURE = 'panel.adventure';

    /**
     * Sends the user to a random board.
     *
     * Also marks their IP with an adventure token that allows them to post with
     * a nifty icon.
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        if (!$this->option('adventureEnabled')) {
            return abort(404);
        }

        $adventures = BoardAdventure::select('board_uri')
            ->where('adventurer_ip', new IP())
            ->get();

        $board_uris = [];

        foreach ($adventures as $adventure) {
            $board_uris[] = $adventure->board_uri;
        }

        $board = Board::select('board_uri')
            ->whereNotIn('board_uri', $adventures)
            ->wherePublic()
            ->whereIndexed()
            ->whereLastPost(48)
            ->get();

        if (count($board)) {
            $board = $board->random(1);

            $newAdventure = new BoardAdventure([
                'board_uri' => $board->board_uri,
                'adventurer_ip' => new IP(),
            ]);
            $newAdventure->expires_at = $newAdventure->freshTimestamp()->addHours(1);
            $newAdventure->save();
        } else {
            $board = false;
        }

        return $this->makeView(static::VIEW_ADVENTURE, [
            'board' => $board,
        ]);
    }
}
