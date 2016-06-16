<?php

namespace App\Http\Controllers\Board;

use App\Ban;
use App\Board;
use App\Post;
use App\Report;
use App\Http\Controllers\Controller;
use App\Services\ContentFormatter;
use App\Support\IP;
use Input;
use Request;
use Validator;
use Session;
use Event;
use App\Events\PostWasBanned;
use App\Events\PostWasModerated;

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

    public function moderate(Request $request, Board $board, Post $post)
    {
        $ban = Input::get('ban', false);
        $delete = Input::get('delete', false);
        $all = Input::get('scope', false) === "all";
        $global = Input::get('scope', false) === "global";

        if (!$ban && !$delete) {
            return abort(400);
        }

        $modActions = [];

        if ($ban) {
            $modActions[] = "ban";

            if ($global) {
                $modActions[] = "global";

                if (!$this->user->canBanGlobally()) {
                    return abort(403);
                }
            } else {
                if ($all) {
                    $modActions[] = "all";
                }

                if (!$this->user->canBan($board)) {
                    return abort(403);
                }
            }

        }
        if ($delete) {
            $modActions[] = "delete";

            if ($global) {
                $modActions[] = "global";

                if (!$this->user->canDeleteGlobally()) {
                    return abort(403);
                }
            } else {
                if ($all) {
                    $modActions[] = "all";
                }

                if (!$this->user->canDelete($post)) {
                    return abort(403);
                }
            }
        }

        $modActions = array_unique($modActions);
        sort($modActions);

        return $this->view(static::VIEW_MOD, [
            'actions' => $modActions,
            'form' => 'ban',
            'board' => $board,
            'post' => $post,

            'ban' => $ban,
            'delete' => $delete,
            'scope' => Input::get('scope', false),

            'banMaxLength' => $this->option('banMaxLength'),
        ]);
    }


    public function issue(Request $request, Board $board, Post $post)
    {
        if (!$post->exists) {
            abort(404);
        }

        $ban = Input::get('ban', false);
        $delete = Input::get('delete', false);
        $all = Input::get('scope', false) === "all";
        $global = Input::get('scope', false) === "global";

        // Create ban.
        if ($ban) {
            if ($global && !$this->user->canBanGlobally()) {
                return abort(403);
            } elseif (!$board->canBan($this->user)) {
                return abort(403);
            }

            $validator = Validator::make(Input::all(), [
                'raw_ip' => 'required|boolean',
                'ban_ip' => 'required_if:raw_ip,true|ip',
                'ban_ip_range' => 'required|between:0,128',
                'justification' => 'max:255',
                'expires_days' => 'required|integer|min:0|max:'.$this->option('banMaxLength'),
                'expires_hours' => 'required|integer|min:0|max:23',
                'expires_minutes' => 'required|integer|min:0|max:59',
            ]);

            if (!$validator->passes()) {
                return redirect()
                    ->back()
                    ->withInput(Input::all())
                    ->withErrors($validator->errors());
            }

            $banLengthStr = [];
            $expiresDays = Input::get('expires_days');
            $expiresHours = Input::get('expires_hours');
            $expiresMinutes = Input::get('expires_minutes');

            if ($expiresDays > 0) {
                $banLengthStr[] = "{$expiresDays}d";
            }
            if ($expiresHours > 0) {
                $banLengthStr[] = "{$expiresHours}h";
            }
            if ($expiresMinutes > 0) {
                $banLengthStr[] = "{$expiresMinutes}m";
            }
            if ($expiresDays == 0 && $expiresHours == 0 && $expiresMinutes == 0) {
                $banLengthStr[] = '&Oslash;';
            }

            $banLengthStr = implode($banLengthStr, ' ');

            // If we're banning without the ability to view IP addresses, we will get our address directly from the post in human-readable format.
            $banIpAddr = $this->user->canViewRawIP() ? Input::get('ban_ip') : $post->getAuthorIpAsString();
            // The CIDR is passed from our post parameters. By default, it is 32/128 for IPv4/IPv6 respectively.
            $banCidr = Input::get('ban_ip_range');
            // This generates a range from start to finish. I.E. 192.168.1.3/22 becomes [192.168.0.0, 192.168.3.255].
            // If we just pass the CDIR into the construct, we get 192.168.1.3-129.168.3.255 for some reason.
            $banCidrRange = IP::cidr_to_range("{$banIpAddr}/{$banCidr}");
            // We then pass this range into the construct method.
            $banIp = new IP($banCidrRange[0], $banCidrRange[1]);

            $banModel = new Ban();
            $banModel->ban_ip_start = $banIp->getStart();
            $banModel->ban_ip_end = $banIp->getEnd();
            $banModel->seen = false;
            $banModel->created_at = $banModel->freshTimestamp();
            $banModel->updated_at = clone $banModel->created_at;
            $banModel->expires_at = clone $banModel->created_at;
            $banModel->expires_at = $banModel->expires_at->addDays($expiresDays);
            $banModel->expires_at = $banModel->expires_at->addHours($expiresHours);
            $banModel->expires_at = $banModel->expires_at->addMinutes($expiresMinutes);
            $banModel->mod_id = $this->user->user_id;
            $banModel->post_id = $post->post_id;
            $banModel->ban_reason_id = null;
            $banModel->justification = Input::get('justification');
            $banModel->board_uri = $global ? null : $board->board_uri;
        }

        // Delete content
        if ($delete) {
            // Delete all posts globally.
            if ($global) {
                if (!$this->user->canDeleteGlobally()) {
                    return abort(403);
                }

                $posts = Post::whereAuthorIP($post->author_ip)
                    ->with('reports')
                    ->get();

                $this->log('log.post.delete.global', $post, [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'ip' => $post->getAuthorIpAsString(),
                    'posts' => $posts->count(),
                ]);

                Post::whereIn('post_id', $posts->pluck('post_id'))->delete();

                foreach ($posts as $post) {
                    Event::fire(new PostWasModerated($post, $this->user));
                }
            }
            // Delete posts locally
            else {
                if (!$this->user->canDelete($post)) {
                    return abort(403);
                }

                // Delete all posts on board
                if ($all) {
                    $posts = Post::whereAuthorIP($post->author_ip)
                        ->where('board_uri', $board->board_uri)
                        ->with('reports')
                        ->get();

                    $this->log('log.post.delete.local', $post, [
                        'board_id' => $post->board_id,
                        'board_uri' => $post->board_uri,
                        'ip' => $post->getAuthorIpAsString(),
                        'posts' => $posts->count(),
                    ]);

                    Post::whereIn('post_id', $posts->pluck('post_id'))->delete();

                    foreach ($posts as $post) {
                        Event::fire(new PostWasModerated($post, $this->user));
                    }
                }
                // Delete a single post
                else {
                    if (!$post->isAuthoredByClient()) {
                        if ($ban) {
                            $this->log('log.post.ban.delete', $post, [
                                'board_id' => $post->board_id,
                                'board_uri' => $post->board_uri,
                                'ip' => $post->getAuthorIpAsString(),
                                'justification' => $banModel->justification,
                                'time' => $banLengthStr,
                                'posts' => 1,
                            ]);
                        } elseif ($post->reply_to) {
                            $this->log('log.post.delete.reply', $post, [
                                'board_id' => $post->board_id,
                                'board_uri' => $post->board_uri,
                                'op_id' => $post->op->board_id,
                            ]);
                        } else {
                            $this->log('log.post.delete.op', $post, [
                                'board_id' => $post->board_id,
                                'board_uri' => $post->board_uri,
                                'replies' => $post->replies()->count(),
                            ]);
                        }
                    }
                }

                $post->delete();

                Event::fire(new PostWasModerated($post, $this->user));
            }
        }

        if ($ban) {
            $banModel->save();

            if ($global) {
                $this->log('log.post.ban.global', $post, [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'ip' => $post->getAuthorIpAsString(),
                    'justification' => $banModel->justification,
                    'time' => $banLengthStr,
                ]);
            } else {
                $this->log('log.post.ban.local', $post, [
                    'board_id' => $post->board_id,
                    'board_uri' => $post->board_uri,
                    'ip' => $post->getAuthorIpAsString(),
                    'justification' => $banModel->justification,
                    'time' => $banLengthStr,
                ]);
            }

            Event::fire(new PostWasBanned($post));

            if (!$delete) {
                Event::fire(new PostWasModerated($post, $this->user));
            }
        }

        if ($delete) {
            return redirect($board->getUrl());
        }

        return redirect($post->getUrl());
    }

    /**
     * Renders the post edit form.
     */
    public function edit(Request $request, Board $board, Post $post)
    {
        if (!$post->exists) {
            abort(404);
        }

        if ($post->canEdit($this->user)) {
            return $this->view(static::VIEW_EDIT, [
                'actions' => ['edit'],
                'form' => 'edit',
                'board' => $board,
                'post' => $post,
            ]);
        }

        return abort(403);
    }

    /**
     * Updates a post with the edit.
     */
    public function update(Request $request, Board $board, Post $post)
    {
        if (!$post->exists) {
            abort(404);
        }

        if ($post->canEdit($this->user)) {
            $post->subject = Input::get('subject');
            $post->email = Input::get('email');
            $post->author = Input::get('author');
            $post->body = Input::get('body');
            $post->body_parsed = null;
            $post->body_parsed_at = null;
            $post->body_html = null;
            $post->updated_by = $this->user->user_id;

            $post->save();

            $this->log('log.post.edit', $post, [
                'board_id' => $post->board_id,
                'board_uri' => $post->board_uri,
            ]);

            return $this->view(static::VIEW_EDIT, [
                'actions' => ['edit'],
                'form' => 'edit',
                'board' => $board,
                'post' => $post,
            ]);
        }

        return abort(403);
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

        $ContentFormatter = new ContentFormatter();
        $reportText = '';

        if ($global === 'global') {
            if (!$post->canReportGlobally($this->user)) {
                abort(403);
            }

            $actions[] = 'global';
            $reportText = $ContentFormatter->formatReportText($this->option('globalReportText'));
        } else {
            if (!$post->canReport($this->user)) {
                abort(403);
            }

            $reportText = $ContentFormatter->formatReportText($board->getConfig('boardReportText'));
        }

        if (!isset($report)) {
            $report = Report::where('post_id', '=', $post->post_id)
                ->where('global', $global === 'global')
                ->where('board_uri', $board->board_uri)
                ->whereByIPOrUser($this->user)
                ->first();
        }

        return $this->view(static::VIEW_MOD, [
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
            if (!$post->canReportGlobally($this->user)) {
                abort(403);
            }

            $actions[] = 'global';
        } elseif (!$post->canReport($this->user)) {
            abort(403);
        }

        $input = Input::all();
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
                ->withInput(Input::except('captcha'))
                ->withErrors($validator->errors());
        }

        // We only want to update a report if it already exists.
        // The unique key here is (global, post, ip).
        $report = Report::firstOrNew([
            'global' => $global === 'global',
            'post_id' => $post->post_id,
            'reporter_ip' => new IP(),
        ]);

        $report->board_uri = $board->board_uri;
        $report->reason = $input['reason'];
        $report->user_id = (bool) Input::get('associate', false) ? $this->user->user_id : null;
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
        if (!$this->user->canFeatureGlobally($post)) {
            return abort(403);
        }

        $post->featured_at = \Carbon\Carbon::now();
        $post->save();

        return redirect('/');
    }

    /**
     * Locks a thread.
     */
    public function lock(Request $request, Board $board, Post $post, $lock = true)
    {
        if (!$post->exists) {
            abort(404);
        }

        if ($post->canLock($this->user)) {
            $post->setLocked($lock !== false)->save();

            $this->log($lock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
                'board_id' => $post->board_id,
                'board_uri' => $post->board_uri,
            ]);

            return $post->redirect();
        }

        return abort(403);
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
        if (!$post->exists) {
            abort(404);
        }

        if ($post->canBumplock($this->user)) {
            $post->setBumplock($bumplock !== false)->save();

            $this->log($bumplock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
                'board_id' => $post->board_id,
                'board_uri' => $post->board_uri,
            ]);

            return $post->redirect();
        }

        return abort(403);
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
        if (!$post->exists) {
            abort(404);
        }

        if ($post->canSticky($this->user)) {
            $post->setSticky($sticky !== false)->save();

            $this->log($sticky ? 'log.post.sticky' : 'log.post.unsticky', $post, [
                'board_id' => $post->board_id,
                'board_uri' => $post->board_uri,
            ]);

            return redirect("{$board->board_uri}/thread/{$post->board_id}");
        }

        return abort(403);
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
