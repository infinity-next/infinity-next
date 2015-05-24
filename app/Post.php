<?php namespace App;

use App\Services\ContentFormatter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Request;

class Post extends Model {
	
	use SoftDeletes;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'posts';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'post_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'board_id', 'reply_to', 'author_ip', 'subject', 'author', 'email', 'body'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['author_ip'];
	
	/**
	 * 
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];
	
	
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// When creating a post in reply to a thread,
		// update its last reply timestamp and add to its reply total.
			/*
		static::creating(function($post)
		{
			$posts_total = 0;
			
			$post->reply_last = $post->freshTimestamp();
			$post->setCreatedAt($post->reply_last);
			$post->setUpdatedAt($post->reply_last);
			
			DB::transaction(function() use ($post)
			{
				DB::table('boards')
					->where('board_uri', $post->board_uri)
					->increment('posts_total');
				
				if ($post->reply_to)
				{
					$reply_count = DB::table('posts')
						->where('post_id', $post->reply_to)
						->increment('reply_count');
				}
				
				$post->board_id = $posts_total;
			});
			
			return true;
		});
			$board = $post->board;
			$board->posts_total += 1;
			$post->board_id = $board->posts_total;
			
			$post->reply_last = $post->freshTimestamp();
			$post->setCreatedAt($post->reply_last);
			$post->setUpdatedAt($post->reply_last);
			
			if ($post->reply_to)
			{
				$op = $post->getOp();
				
				if ($op && $op->canReply())
				{
					$op->reply_count += 1;
					$op->reply_last = $post->created_at;
					$op->save();
				}
				else
				{
					return false;
				}
			}
			$board->save();*/
		
		// When deleting a post, delete its children.
		static::deleting(function($post)
		{
			static::replyTo($post->post_id)->delete();
		});
		
		static::deleted(function($post) {
			// Subtract a reply from OP and update its last reply time.
			if ($post->reply_to)
			{
				$lastReply = $post->op->getReplyLast();
				
				if ($lastReply)
				{
					$post->op->reply_last = $lastReply->created_at;
				}
				else
				{
					$post->op->reply_last = $post->op->created_at;
				}
				
				$post->op->reply_count -= 1;
				$post->op->save();
			}
		});
	}
	
	public function canEdit($user)
	{
		return $user->canEdit($this);
	}
	
	public function canDelete($user)
	{
		return $user->canDelete($this);
	}
	
	public function canReply($user = null)
	{
		if (!is_null($user))
		{
			return $user->canReply($this);
		}
		
		return true;
	}
	
	public function canSticky($user)
	{
		return $user->canSticky($this);
	}
	
	public function getBodyFormatted()
	{
		$ContentFormatter = new ContentFormatter();
		return $ContentFormatter->formatPost($this);
	}
	
	
	public function attachments()
	{
		return $this->belongsToMany("\App\FileStorage", 'file_attachments', 'post_id', 'file_id')->withPivot('filename');
	}
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
	public function capcode()
	{
		return $this->hasOne('\App\Role', 'role_id', 'capcode_id');
	}
	
	public function op()
	{
		return $this->belongsTo('\App\Post', 'reply_to', 'post_id');
	}
	
	public function replies()
	{
		return $this->hasMany('\App\Post', 'reply_to', 'post_id');
	}
	
	public function editor()
	{
		return $this->hasOne('\App\User', 'user_id', 'updated_by');
	}
	
	
	public function getBoard()
	{
		return $this->board()
			->get()
			->first();
	}
	
	public static function getPostForBoard($uri, $board_id)
	{
		return static::where([
				'board_uri' => $uri,
				'board_id' => $board_id,
			])
			->first();
	}
	
	public static function getThread($post)
	{
		return $this->posts()
			->with('attachments', 'replies', 'replies.attachments', 'capcode', 'replies.capcode')
			->andBan()
			->andEditor()
			->op()
			->visible()
			->orderBy('reply_last', 'desc')
			->where('board_id', $post)
			->orWhere('reply_to', $post)
			->get();
	}
	
	public function getOp()
	{
		return $this->op()
			->get()
			->first();
	}
	
	
	/**
	 * Returns the latest reply to a post.
	 *
	 * @return Post|null
	 */
	public function getReplyLast()
	{
		return $this->replies()
			->visible()
			->orderBy('post_id', 'desc')
			->take(1)
			->get()
			->first();
	}
	
	public function getReplies()
	{
		return $this->replies()
			->get();
	}
	
	public function getRepliesForIndex()
	{
		return $this->replies()
			->forIndex()
			->get()
			->reverse();
	}
	
	public function setSticky($sticky = true)
	{
		if ($sticky)
		{
			$this->stickied = true;
			$this->stickied_at = $this->freshTimestamp();
		}
		else
		{
			$this->stickied = false;
			$this->stickied_at = null;
		}
		
		return $this;
	}
	
	
	public function scopeAndBan($query)
	{
		return $query
			->leftJoin('bans', function($join)
			{
				$join->on('bans.post_id', '=', 'posts.post_id');
			})
			->addSelect(
				'bans.ban_id',
				'bans.justification as ban_reason'
			);
	}
	public function scopeAndEditor($query)
	{
		return $query
			->leftJoin('users', function($join)
			{
				$join->on('users.user_id', '=', 'posts.updated_by');
			})
			->addSelect(
				'posts.*',
				'users.username as updated_by_username'
			);
	}
	
	public function scopeOp($query)
	{
		return $query->where('reply_to', null);
	}
	
	public function scopeRecent($query)
	{
		return $query->where('created_at', '>=', static::freshTimestamp()->subHour());
	}
	
	public function scopeForIndex($query)
	{
		return $query->visible()
			->orderBy('post_id', 'desc')
			->take( $this->stickied_at ? 1 : 5 );
	}
	
	public function scopeReplyTo($query, $replies = false)
	{
		if ($replies instanceof \Illuminate\Database\Eloquent\Collection)
		{
			$thread_ids = [];
			
			foreach ($replies as $thread)
			{
				$thread_ids[] = (int) $thread->post_id;
			}
			
			return $query->whereIn('reply_to', $thread_ids);
		}
		else if (is_numeric($replies))
		{
			return $query->where('reply_to', '=', $replies);
		}
		else
		{
			return $query->where('reply_to', 'not', null);
		}
	}
	
	public function scopeVisible($query)
	{
		return $query->where('deleted_at', null);
	}
	
	public function transact()
	{
		$post = &$this;
		if (!isset($post->board_id))
		{
			$post->reply_last = $post->freshTimestamp();
			$post->setCreatedAt($post->reply_last);
			$post->setUpdatedAt($post->reply_last);
			
			DB::transaction(function() use ($post)
			{
				DB::table('boards')
					->where('board_uri', $post->board_uri)
					->increment('posts_total');
				
				$boards = DB::table('boards')
					->where('board_uri', $post->board_uri)
					->lockForUpdate()
					->select('posts_total')
					->get();
				
				$posts_total = $boards[0]->posts_total;
				
				if ($post->reply_to)
				{
					$reply_count = DB::table('posts')
						->where('post_id', $post->reply_to)
						->increment('reply_count');
				}
				
				$post->board_id = $posts_total;
				$post->save();
			});
		}
	}
}
