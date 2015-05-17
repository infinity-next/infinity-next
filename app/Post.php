<?php namespace App;

use App\Services\ContentFormatter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
		static::creating(function($post)
		{
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
			
			$board->save();
			return true;
		});
		
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
	
	public function op()
	{
		return $this->belongsTo('\App\Post', 'reply_to', 'post_id');
	}
	
	public function replies()
	{
		return $this->hasMany('\App\Post', 'reply_to', 'post_id');
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
			->with('attachments', 'replies', 'replies.attachments')
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
}
