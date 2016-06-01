<?php

namespace App\Http\Controllers\Panel;

use App\Ban;
use App\Board;
use Input;
use Request;
use Validator;

class BansController extends PanelController
{
    /*
    |--------------------------------------------------------------------------
    | Bans Controller
    |--------------------------------------------------------------------------
    |
    | This controller will list active bans applied to your IP addresses and
    | allow you to appeal them and review the status of your appeal.
    |
    */

    const VIEW_BANS = 'panel.bans';
    const VIEW_BAN = 'panel.ban';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.home';

    /**
     * Lists the site's bans.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        $bans = Ban::orderBy('ban_id', 'desc')
            ->paginate(15);

        return $this->view(static::VIEW_BANS, [
            'bans' => $bans,
            'clientOnly' => false,
        ]);
    }

    /**
     * Lists bans for this connection.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndexForSelf()
    {
        $bans = Ban::getBans(Request::ip(), false, false)
            ->paginate(15);

        return $this->view(static::VIEW_BANS, [
            'bans' => $bans,
            'clientOnly' => true,
        ]);
    }

    /**
     * Lists a ban.
     *
     * Will also update its "seen" if it was not seen prior.
     *
     * @param  \App\Ban    $ban
     *
     * @return \Illuminate\Http\Response
     */
    public function getBan(Ban $ban)
    {
        if (!$ban->canView($this->user)) {
            return abort(403);
        }

        $seeing = false;

        if (!$ban->seen) {
            $ban->seen = true;
            $ban->save();
            $seeing = true;
        }

        return $this->view(static::VIEW_BAN, [
            'ban' => $ban,
            'seeing' => $seeing,
        ]);
    }

    /**
     * Lists a board's bans.
     *
     * @param  \App\Board  $board
     *
     * @return \Illuminate\Http\Response
     */
    public function getBoardIndex(Board $board)
    {
        $bans = Ban::orderBy('ban_id', 'desc')
            ->where('board_uri', $board->board_uri)
            ->paginate(15);

        return $this->view(static::VIEW_BANS, [
            'bans' => $bans,
            'clientOnly' => false,
        ]);
    }

    /**
     * Submits an appeal for a ban.
     *
     * @param  \App\Ban    $ban
     *
     * @return \Illuminate\Http\Response
     */
    public function putAppeal(Ban $ban)
    {
        if (!$ban->canAppeal() || !$ban->isBanForIP()) {
            return abort(403);
        }

        $input = Input::all();
        $validator = Validator::make($input, [
            'appeal_text' => [
                'string',
                'between:0,2048',
            ],
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withErrors($validator->errors());
        }

        $appeal = $ban->appeals()->create([
            'appeal_ip' => inet_pton(Request::ip()),
            'appeal_text' => $input['appeal_text'],
        ]);
        $ban->setRelation('appeals', $ban->appeals->push($appeal));

        return redirect($ban->getUrl());
    }
}
