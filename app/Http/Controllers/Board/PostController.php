<?php

namespace App\Http\Controllers\Board;

use App\Ban;
use App\Board;
use App\BoardAsset;
use App\Post;
use App\Report;
use App\Events\BanWasCreated;
use App\Events\PostWasModerated;
use App\Http\Controllers\Controller;
use App\Jobs\PostModeration;
use App\Services\ContentFormatter;
use App\Support\IP;
use Gate;
use Request;
use Session;
use Validator;
use Event;

/**
 * Post moderation and management.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class PostController extends Controller
{
    const VIEW_EDIT = 'board.post.mod';
    const VIEW_MOD  = 'board.post.mod';

    public function moderate(Board $board, Post $post)
    {
        if (!Gate::any(['delete', 'ban'], $post)) {
            return abort(403);
        }

        //$banBoards = user()->canInBoards('board.user.ban.free');
        //$deleteBoards = user()->canInBoards('board.post.delete.other');
        //$boardsWithRights = Board::whereIn('board_uri', array_unique(array_merge($banBoards, $deleteBoards)))->get();

        return $this->makeView(static::VIEW_MOD, [
            'form' => "ban",
            'board' => $board,
            'post' => $post,
            'banMaxLength' => $this->option('banMaxLength'),
            //'boardsWithRights' => $boardsWithRights,
        ]);
    }


    public function issue(Board $board, Post $post)
    {
        $validator = Validator::make(Request::all(), [
            'delete' => 'required|digits_between:0,2',

            'ban' => 'required_if:delete,0|bool',
            'ban_ip_range' => 'required_if:ban,1|digits_between:0,128',
            'justification' => 'nullable|string|max:1024',
            'expires_days' => 'required_if:ban,1|digits_between:0,30',
            'expires_hours' => 'required_if:ban,1|digits_between:0,24',
            'expires_minutes' => 'required_if:ban,1|digits_between:0,60',

            'scope' => "required|string|in:_global,{$board->board_uri}",
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // set markers for what we're doing.
        $ban = !!Request::input('ban');
        $delete = !!Request::input('delete');
        $deleteAll = Request::input('delete') == 2;
        $global = Request::input('scope') == '_global';

        // authorization
        if ($ban) {
            if ($global) {
                $this->authorize('global-ban');
            }
            else {
                $this->authorize('ban', $board);
            }

        }
        if ($delete) {
            if ($global) {
                $this->authorize('global-delete');
            }
            else {
                $this->authorize('delete', $post);
            }
        }

        PostModeration::dispatch(user(), new IP, $post, Request::input());

        // redirect to whatever is most relevant.
        if ($delete) {
            if (!$post->isOp()) {
                return redirect($post->thread->getUrl());
            }
            else {
                return redirect($board->getUrl());
            }
        }
        else {
            return redirect($post->getUrl());
        }
    }

    /**
     * Renders the post edit form.
     */
    public function edit(Request $request, Board $board, Post $post)
    {
        $this->authorize('edit', $post);

        return $this->makeView(static::VIEW_EDIT, [
            'actions' => ['edit'],
            'form' => 'edit',
            'board' => $board,
            'post' => $post,
        ]);
    }

    /**
     * Updates a post with the edit.
     */
    public function update(Board $board, Post $post)
    {
        $this->authorize('edit', $post);

        $post->subject = Request::input('subject');
        $post->email = Request::input('email');
        $post->author = Request::input('author');
        $post->body = Request::input('body');
        $post->body_parsed = null;
        $post->body_parsed_at = null;
        $post->body_html = null;
        $post->updated_by = user()->user_id;

        $post->save();

        return $this->makeView(static::VIEW_EDIT, [
            'actions' => ['edit'],
            'form' => 'edit',
            'board' => $board,
            'post' => $post,
        ]);
    }

    /**
     * Renders the post edit form.
     */
    public function report(Request $request, Board $board, Post $post, $global = false)
    {
        if (!$post->exists) {
            abort(404);
        }

        $actions = ['report'];

        $ContentFormatter = new ContentFormatter;
        $reportText = '';

        if ($global === 'global') {
            $this->authorize('create-global', Report::class);

            $actions[] = 'global';
            $reportText = $ContentFormatter->formatReportText($this->option('globalReportText'));
        }
        else {
            $this->authorize('report', $post);

            $reportText = $ContentFormatter->formatReportText($board->getConfig('boardReportText'));
        }

        if (!isset($report)) {
            $report = Report::where('post_id', '=', $post->post_id)
                ->where('global', $global === 'global')
                ->where('board_uri', $board->board_uri)
                ->whereByIpOrUser(user())
                ->first();
        }

        return $this->makeView(static::VIEW_MOD, [
            'actions' => $actions,
            'form' => 'report',
            'board' => $board,
            'post' => $post,
            'report' => $report ?: false,
            'reportText' => $reportText,
            'reportGlobal' => $global === 'global',
        ]);
    }

    /**
     * Submits a report.
     */
    public function flag(Request $request, Board $board, Post $post, $global = false)
    {
        if (!$post->exists) {
            abort(404);
        }

        if ($global === 'global') {
            $this->authorize('create-global', Report::class);
            $actions[] = 'global';
        }
        else {
            $this->authorize('report', $post);
        }

        $input = Request::all();
        $validator = Validator::make($input, [
            'associate' => [
                'boolean',
            ],

            'captcha' => [
                'required',
                'captcha',
            ],

            'reason' => [
                'string',
                'between:0,512',
            ],
        ]);

        if (!$validator->passes()) {
            return redirect()
                ->back()
                ->withInput(Request::except('captcha'))
                ->withErrors($validator->errors());
        }

        // We only want to update a report if it already exists.
        // The unique key here is (global, post, ip).
        $report = Report::firstOrNew([
            'global' => $global === 'global',
            'post_id' => $post->post_id,
            'reporter_ip' => (new IP)->toText(),
        ]);

        $report->board_uri = $board->board_uri;
        $report->reason = $input['reason'];
        $report->user_id = (bool) Request::input('associate', false) ? user()->user_id : null;
        $report->is_dismissed = false;
        $report->is_successful = false;
        $report->save();

        Session::flash('success', trans('board.report.success'));

        return back()->with('report', $report);
    }

    /**
     * Features a post.
     */
    public function feature(Request $request, Board $board, Post $post, $global = false)
    {
        $this->authorize('feature', $post);

        $post->featured_at = \Carbon\Carbon::now();
        $post->save();

        return redirect('/');
    }

    /**
     * Locks a thread.
     */
    public function lock(Request $request, Board $board, Post $post, $lock = true)
    {
        $this->authorize('lock', $post);
        $post->setLocked($lock !== false)->save();

        $this->log($lock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
            'board_id' => $post->board_id,
            'board_uri' => $post->board_uri,
        ]);

        return $post->redirect();
    }

    /**
     * Unlocks a thread.
     */
    public function unlock(Request $request, Board $board, Post $post)
    {
        // Redirect to anyBumplock with a flag denoting an unlock.
        return $this->unlock($request, $board, $post, false);
    }

    /**
     * Bumplocks a thread.
     */
    public function bumplock(Request $request, Board $board, Post $post, $bumplock = true)
    {
        $this->authorize('bumplock', $post);

        $post->setBumplock($bumplock !== false)->save();

        $this->log($bumplock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
            'board_id' => $post->board_id,
            'board_uri' => $post->board_uri,
        ]);

        return $post->redirect();
    }

    /**
     * Un-bumplocks a thread.
     */
    public function unbumplock(Request $request, Board $board, Post $post)
    {
        // Redirect to anyBumplock with a flag denoting an unbumplock.
        return $this->bumplock($request, $board, $post, false);
    }

    /**
     * Stickies a thread.
     */
    public function sticky(Request $request, Board $board, Post $post, $sticky = true)
    {
        $this->authorize('sticky', $post);

        $post->setSticky($sticky !== false)->save();

        $this->log($sticky ? 'log.post.sticky' : 'log.post.unsticky', $post, [
            'board_id' => $post->board_id,
            'board_uri' => $post->board_uri,
        ]);

        return redirect("{$board->board_uri}/thread/{$post->board_id}");
    }

    /**
     * Unstickies a thread.
     */
    public function unsticky(Request $request, Board $board, Post $post)
    {
        // Redirect to anySticky with a flag denoting an unsticky.
        return $this->sticky($request, $board, $post, false);
    }

    /**
     * Generates HTML content with post input.
     */
    public function preview(Request $request, Board $board)
    {
        $body = $request->input('body');

        return json_encode([
            'html' => $ContentFormatter->formatPost($this),
        ]);
    }
}
