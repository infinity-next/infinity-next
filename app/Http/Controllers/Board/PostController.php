<?php namespace App\Http\Controllers\Board;

use App\Ban;
use App\Board;
use App\Post;
use App\Report;
use App\Http\Controllers\Controller;
use App\Services\ContentFormatter;
use App\Support\IP\CIDR;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Input;
use Request;
use Validator;
use Session;

use Event;
use App\Events\PostWasBanned;
use App\Events\PostWasModerated;

class PostController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Post Controller
	|--------------------------------------------------------------------------
	|
	| Any request to be acted upon a single, extant post is carried out through
	| this form.
	| 
	| Note that creating a NEW post against a board is handled
	| in the BoardController instead.
	| 
	*/
	
	const VIEW_EDIT = "board.post.mod";
	const VIEW_MOD  = "board.post.mod";
	
	/**
	 * 
	 */
	public function getMod(Request $request, Board $board, Post $post)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		// Take trailing arguments,
		// compare them against a list of real actions,
		// intersect the liss to find the true commands.
		$actions    = ["delete", "ban", "all", "global"];
		$argList    = func_get_args();
		$modActions = array_intersect($actions, array_splice($argList, 2));
		sort($modActions);
		
		$ban        = in_array("ban",    $modActions);
		$delete     = in_array("delete", $modActions);
		$all        = in_array("all",    $modActions);
		$global     = in_array("global", $modActions);
		
		if (!$ban && !$delete)
		{
			return abort(404);
		}
		
		if ($ban)
		{
			if ($global && !$this->user->canBanGlobally())
			{
				return abort(403);
			}
			else if (!$this->user->canBan($board))
			{
				return abort(403);
			}
			
			return $this->view(static::VIEW_MOD, [
				"actions"      => $modActions,
				"form"         => "ban",
				"board"        => $board,
				"post"         => $post,
				
				"banMaxLength" => $this->option('banMaxLength'),
			]);
		}
		else if ($delete)
		{
			if ($global)
			{
				if (!$this->user->canDeleteGlobally())
				{
					return abort(403);
				}
				
				
				$posts = Post::ip($post->author_ip)
					->with('reports')
					->get();
				
				$this->log('log.post.delete.global', $post, [
					"board_id"  => $post->board_id,
					"board_uri" => $post->board_uri,
					"ip"        => $post->getAuthorIpAsString(),
					"posts"     => $posts->count(),
				]);
				
				
				Post::whereIn('post_id', $posts->pluck('post_id'))->delete();
				
				foreach ($posts as $post)
				{
					Event::fire(new PostWasModerated($post, $this->user));
				}
				
				return redirect($board->board_uri);
			}
			else
			{
				if (!$this->user->canDelete($post))
				{
					return abort(403);
				}
				
				if ($all)
				{
					$posts = Post::ip($post->author_ip)
						->where('board_uri', $board->board_uri)
						->with('reports')
						->get();
					
					$this->log('log.post.delete.local', $post, [
						"board_id"  => $post->board_id,
						"board_uri" => $post->board_uri,
						"ip"        => $post->getAuthorIpAsString(),
						"posts"     => $posts->count(),
					]);
					
					Post::whereIn('post_id', $posts->pluck('post_id'))->delete();
					
					foreach ($posts as $post)
					{
						Event::fire(new PostWasModerated($post, $this->user));
					}
					
					return redirect($board->board_uri);
				}
				else
				{
					if (!$post->isAuthoredByClient())
					{
						if ($post->reply_to)
						{
							$this->log('log.post.delete.reply', $post, [
								"board_id"  => $post->board_id,
								"board_uri" => $post->board_uri,
								"op_id"     => $post->op->board_id,
							]);
						}
						else
						{
							$this->log('log.post.delete.op', $post, [
								"board_id"  => $post->board_id,
								"board_uri" => $post->board_uri,
								"replies"   => $post->replies()->count(),
							]);
						}
					}
					
					$post->delete();
					
					Event::fire(new PostWasModerated($post, $this->user));
					
					if ($post->reply_to)
					{
						return redirect("{$post->board_uri}/thread/{$post->op->board_id}");
					}
					else
					{
						return redirect($board->board_uri);
					}
				}
			}
		}
		
		return abort(403);
	}
	
	/**
	 *
	 */
	public function putMod(Request $request, Board $board, Post $post)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		// Take trailing arguments,
		// compare them against a list of real actions,
		// intersect the liss to find the true commands.
		$actions    = ["delete", "ban", "all", "global"];
		$argList    = func_get_args();
		$modActions = array_intersect($actions, array_splice($argList, 2));
		sort($modActions);
		
		$ban        = in_array("ban",    $modActions);
		$delete     = in_array("delete", $modActions);
		$all        = in_array("all",    $modActions);
		$global     = in_array("global", $modActions);
		
		if (!$ban)
		{
			return abort(404);
		}
		
		
		$validator = Validator::make(Input::all(), [
			'raw_ip'          => 'required|boolean',
			'ban_ip'          => 'required_if:raw_ip,true|ip',
			'ban_ip_range'    => 'required|between:0,128',
			'justification'   => 'max:255',
			'expires_days'    => 'required|integer|min:0|max:' . $this->option('banMaxLength'),
			'expires_hours'   => 'required|integer|min:0|max:23',
			'expires_minutes' => 'required|integer|min:0|max:59',
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withInput(Input::all())
				->withErrors($validator->errors());
		}
		
		$banLengthStr   = [];
		$expiresDays    = Input::get('expires_days');
		$expiresHours   = Input::get('expires_hours');
		$expiresMinutes = Input::get('expires_minutes');
		
		if ($expiresDays > 0)
		{
			$banLengthStr[] = "{$expiresDays}d";
		}
		if ($expiresHours > 0)
		{
			$banLengthStr[] = "{$expiresHours}h";
		}
		if ($expiresMinutes > 0)
		{
			$banLengthStr[] = "{$expiresMinutes}m";
		}
		if ($expiresDays == 0 && $expiresHours == 0 && $expiresMinutes == 0)
		{
			$banLengthStr[] = "&Oslash;";
		}
		
		$banLengthStr = implode($banLengthStr, " ");
		
		// If we're banning without the ability to view IP addresses, we will get our address directly from the post in human-readable format.
		$banIpAddr    = $this->user->canViewRawIP() ? Input::get('ban_ip') : $post->getAuthorIpAsString();
		// The CIDR is passed from our post parameters. By default, it is 32/128 for IPv4/IPv6 respectively.
		$banCidr      = Input::get('ban_ip_range');
		// This generates a range from start to finish. I.E. 192.168.1.3/22 becomes [192.168.0.0, 192.168.3.255].
		// If we just pass the CDIR into the construct, we get 192.168.1.3-129.168.3.255 for some reason.
		$banCidrRange = CIDR::cidr_to_range("{$banIpAddr}/{$banCidr}");
		// We then pass this range into the construct method.
		$banIp        = new CIDR($banCidrRange[0], $banCidrRange[1]);
		
		$ban = new Ban();
		$ban->ban_ip_start  = inet_pton($banIp->getStart());
		$ban->ban_ip_end    = inet_pton($banIp->getEnd());
		$ban->seen          = false;
		$ban->created_at    = $ban->freshTimestamp();
		$ban->updated_at    = clone $ban->created_at;
		$ban->expires_at    = clone $ban->created_at;
		$ban->expires_at->addDays($expiresDays);
		$ban->expires_at->addHours($expiresHours);
		$ban->expires_at->addMinutes($expiresMinutes);
		$ban->mod_id        = $this->user->user_id;
		$ban->post_id       = $post->post_id;
		$ban->ban_reason_id = null;
		$ban->justification = Input::get('justification');
		
		if ($global)
		{
			if (($ban && !$this->user->canBanGlobally()) || ($delete && !$this->user->canDeleteGlobally()))
			{
				return abort(403);
			}
			
			if ($ban)
			{
				$ban->board_uri = null;
				$ban->save();
			}
			
			$this->log('log.post.ban.global', $post, [
				"board_id"      => $post->board_id,
				"board_uri"     => $post->board_uri,
				"ip"            => $post->getAuthorIpAsString(),
				"justification" => $ban->justification,
				"time"          => $banLengthStr,
			]);
			
			if ($delete)
			{
				$posts = Post::ipBinary($post->author_ip);
				
				$this->log('log.post.ban.delete', $post, [
					"board_id"  => $post->board_id,
					"board_uri" => $post->board_uri,
					"posts"     => $posts->count(),
				]);
				
				$posts->delete();
				
				return redirect($board->board_uri);
			}
		}
		else
		{
			if (($ban && !$board->canBan($this->user)) || ($delete && !$board->canDelete($this->user)))
			{
				return abort(403);
			}
			
			if ($ban)
			{
				$ban->board_uri = $post->board_uri;
				$ban->save();
			}
			
			$this->log('log.post.ban.local', $post, [
				"board_id"      => $post->board_id,
				"board_uri"     => $post->board_uri,
				"ip"            => $post->getAuthorIpAsString(),
				"justification" => $ban->justification,
				"time"          => $banLengthStr,
			]);
			
			if ($delete)
			{
				if ($all)
				{
					$posts = Post::ipBinary($post->author_ip)
						->where('board_uri', $board->board_uri);
					
					$this->log('log.post.ban.delete', $post, [
						"board_id"  => $post->board_id,
						"board_uri" => $post->board_uri,
						"posts"     => $posts->count(),
					]);
					
					$posts->delete();
					
					return redirect($board->board_uri);
				}
				else
				{
					$this->log('log.post.ban.delete', $post, [
						"board_id"  => $post->board_id,
						"board_uri" => $post->board_uri,
						"posts"     => 1,
					]);
					
					$post->delete();
					
					if ($post->reply_to)
					{
						return redirect("{$post->board_uri}/thread/{$post->op->board_id}");
					}
					else
					{
						return redirect($board->board_uri);
					}
				}
			}
		}
		
		Event::fire(new PostWasBanned($post));
		Event::fire(new PostWasModerated($post, $this->user));
		
		if ($post->reply_to)
		{
			return redirect("{$post->board_uri}/thread/{$post->op->board_id}#{$post->board_id}");
		}
		else
		{
			return redirect("{$post->board_uri}/thread/{$post->board_id}");
		}
	}
	
	/**
	 * Renders the post edit form.
	 */
	public function getEdit(Request $request, Board $board, Post $post)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		if ($post->canEdit($this->user))
		{
			return $this->view(static::VIEW_EDIT, [
				"actions" => ["edit"],
				"form"    => "edit",
				"board"   => $board,
				"post"    => $post,
			]);
		}
		
		return abort(403);
	}
	
	/**
	 * Updates a post with the edit.
	 */
	public function patchEdit(Request $request, Board $board, Post $post)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		if ($post->canEdit($this->user))
		{
			$post->subject        = Input::get('subject');
			$post->email          = Input::get('email');
			$post->body           = Input::get('body');
			$post->body_parsed    = NULL;
			$post->body_parsed_at = NULL;
			$post->body_html      = NULL;
			$post->updated_by     = $this->user->user_id;
			
			$post->save();
			
			$this->log('log.post.edit', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
			]);
			
			return $this->view(static::VIEW_EDIT, [
				"actions" => ["edit"],
				"form"    => "edit",
				"board"   => $board,
				"post"    => $post,
			]);
		}
		
		return abort(403);
	}
	
	/**
	 * Renders the post edit form.
	 */
	public function getReport(Request $request, Board $board, Post $post, $global = false)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		$actions = ["report"];
		
		$ContentFormatter = new ContentFormatter;
		$reportText = "";
		
		if ($global === "global")
		{
			if (!$post->canReportGlobally($this->user))
			{
				abort(403);
			}
			
			$actions[] = "global";
			$reportText = $ContentFormatter->formatReportText($this->option('globalReportText'));
		}
		else
		{
			if (!$post->canReport($this->user))
			{
				abort(403);
			}
			
			$reportText = $ContentFormatter->formatReportText($board->getConfig('boardReportText'));
		}
		
		if (!isset($report))
		{
			$user   = $this->user;
			$report = Report::where('post_id', '=', $post->post_id)
				->where('global', $global === "global")
				->where('board_uri', $board->board_uri)
				->where(function($query) use ($user)
				{
					$query->where('reporter_ip', inet_pton(Request::ip()));
					
					if (!$user->isAnonymous())
					{
						$query->orWhere('user_id', $user->user_id);
					}
				})
				->first();
		}
		
		return $this->view(static::VIEW_MOD, [
			'actions'      => $actions,
			'form'         => "report",
			'board'        => $board,
			'post'         => $post,
			'report'       => $report ?: false,
			'reportText'   => $reportText,
			'reportGlobal' => $global === "global",
		]);
	}
	
	/**
	 * Updates a post with the edit.
	 */
	public function postReport(Request $request, Board $board, Post $post, $global = false)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		if ($global === "global")
		{
			if (!$post->canReportGlobally($this->user))
			{
				abort(403);
			}
			
			$actions[] = "global";
		}
		else if (!$post->canReport($this->user))
		{
			abort(403);
		}
		
		$input     = Input::all();
		$validator = Validator::make($input, [
			'associate' => [
				"boolean",
			],
			
			'captcha' => [
				"required",
				"captcha",
			],
			
			'reason' => [
				"string",
				"between:0,512",
			],
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withInput(Input::except('captcha'))
				->withErrors($validator->errors());
		}
		
		// We only want to update a report if it already exists.
		// The unique key here is (global, post, ip).
		$report =  Report::firstOrNew([
			'global'      => $global === "global",
			'post_id'     => $post->post_id,
			'reporter_ip' => inet_pton(Request::ip()),
		]);
		
		$report->board_uri     = $board->board_uri;
		$report->reason        = $input['reason'];
		$report->user_id       = !!Input::get('associate', false) ? $this->user->user_id : NULL;
		$report->is_dismissed  = false;
		$report->is_successful = false;
		$report->save();
		
		Session::flash('success', trans('board.report.success'));
		
		return back()->with('report', $report);
	}
	
	/**
	 * Locks a thread.
	 */
	public function anyLock(Request $request, Board $board, Post $post, $lock = true)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		if ($post->canLock($this->user))
		{
			$post->setLocked( $lock )->save();
			
			$this->log($lock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
			]);
			
			return $post->redirect();
		}
		
		return abort(403);
	}
	
	/**
	 * Unlocks a thread.
	 */
	public function anyUnlock(Request $request, Board $board, Post $post)
	{
		// Redirect to anyBumplock with a flag denoting an unlock.
		return $this->anyLock($request, $board, $post, false);
	}
	
	/**
	 * Bumplocks a thread.
	 */
	public function anyBumplock(Request $request, Board $board, Post $post, $bumplock = true)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		if ($post->canBumplock($this->user))
		{
			$post->setBumplock( $bumplock )->save();
			
			$this->log($bumplock ? 'log.post.bumplock' : 'log.post.unbumplock', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
			]);
			
			return $post->redirect();
		}
		
		return abort(403);
	}
	
	/**
	 * Un-bumplocks a thread.
	 */
	public function anyUnbumplock(Request $request, Board $board, Post $post)
	{
		// Redirect to anyBumplock with a flag denoting an unbumplock.
		return $this->anyBumplock($request, $board, $post, false);
	}
	
	/**
	 * Stickies a thread.
	 */
	public function anySticky(Request $request, Board $board, Post $post, $sticky = true)
	{
		if (!$post->exists)
		{
			abort(404);
		}
		
		
		if ($post->canSticky($this->user))
		{
			$post->setSticky( $sticky )->save();
			
			$this->log($sticky ? 'log.post.sticky' : 'log.post.unsticky', $post, [
				"board_id"  => $post->board_id,
				"board_uri" => $post->board_uri,
			]);
			
			return redirect("{$board->board_uri}/thread/{$post->board_id}");
		}
		
		return abort(403);
	}
	
	/**
	 * Unstickies a thread.
	 */
	public function anyUnsticky(Request $request, Board $board, Post $post)
	{
		// Redirect to anySticky with a flag denoting an unsticky.
		return $this->anySticky($request, $board, $post, false);
	}
	
	/**
	 * Generates HTML content with post input.
	 */
	public function anyPreview(Request $request, Board $board)
	{
		$body = $request->input('body');
		
		return json_encode([
			'html' => $ContentFormatter->formatPost($this),
		]);
	}
	
}
