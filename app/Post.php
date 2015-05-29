<?php namespace App;

use App\FileStorage;
use App\FileAttachment;
use App\Services\ContentFormatter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Input;
use File;
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
	protected $fillable = ['board_uri', 'board_id', 'reply_to', 'author_ip', 'capcode_id', 'subject', 'author', 'email', 'body'];
	
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
		
		// When creating a thread, make sure it has a board_id.
		static::creating(function($post)
		{
			return isset($post->board_id);
		});
		
		// When deleting a post, delete its children.
		static::deleting(function($post)
		{
			static::replyTo($post->post_id)->delete();
		});
		
		// After a post is deleted, update OP's reply count.
		static::deleted(function($post) {
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
				'posts.*',
				'bans.ban_id',
				'bans.justification as ban_reason'
			);
	}
	
	public function scopeAndCapcode($query)
	{
		return $query
			->leftJoin('roles', function($join)
			{
				$join->on('roles.role_id', '=', 'posts.capcode_id');
			})
			->addSelect(
				'posts.*',
				'roles.capcode as capcode_capcode',
				'roles.role as capcode_role'
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
	
	public function submitTo(Board &$board, &$thread = null)
	{
		$this->board_uri  = $board->board_uri;
		$this->author_ip  = Request::getClientIp();
		$this->reply_last = $this->freshTimestamp();
		$this->setCreatedAt($this->reply_last);
		$this->setUpdatedAt($this->reply_last);
		
		if (!is_null($thread) && !($thread instanceof Post))
		{
			$thread = $board->getLocalThread($thread);
			$this->reply_to = $thread->post_id;
		}
		
		// Store attachments
		$uploads = [];
		
		if (is_array($files = Input::file('files')))
		{
			$uploads = array_filter($files);
		}
		
		
		// Store the post in the database.
		$post = &$this;
		DB::transaction(function() use ($post)
		{
			// The objective of this transaction is to prevent concurrency issues in the database
			// on the unique joint index [board_uri,board_id] which is generated procedurall
			// alongside the primary autoincrement column post_id.
			
			// First instruction is to add +1 to posts_total.
			DB::table('boards')
				->where('board_uri', $post->board_uri)
				->increment('posts_total');
			
			// Second, we record this value and lock the table.
			$boards = DB::table('boards')
				->where('board_uri', $post->board_uri)
				->lockForUpdate()
				->select('posts_total')
				->get();
			
			$posts_total = $boards[0]->posts_total;
			
			// Optionally, the OP of this thread needs a +1 to reply count.
			if ($post->reply_to)
			{
				DB::table('posts')
					->where('post_id', $post->reply_to)
					->update(['reply_last' => $post->created_at]);
				
				$reply_count = DB::table('posts')
					->where('post_id', $post->reply_to)
					->increment('reply_count');
			}
			
			// Finally, we set our board_id and save.
			$post->board_id = $posts_total;
			$post->save();
			
			// Queries and locks are handled automatically after this closure ends.
		});
		
		
		// Process uploads.
		if (count($uploads) > 0)
		{
			foreach ($uploads as $uploadIndex => $upload)
			{
				$uploadName  = $upload->getClientOriginalName();
				$uploadExt   = pathinfo($uploadName, PATHINFO_EXTENSION);
				
				$fileContent = File::get($upload);
				$fileName    = basename($uploadName, "." . $uploadExt);
				$fileExt     = $upload->guessExtension();
				
				$storage     = FileStorage::storeUpload($upload);
				
				$attachment  = new FileAttachment();
				$attachment->post_id  = $this->post_id;
				$attachment->file_id  = $storage->file_id;
				$attachment->filename = "{$fileName}.{$fileExt}";
				$attachment->save();
			}
		}
	}
}
