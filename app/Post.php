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

use Event;
use App\Events\PostWasAdded;
use App\Events\PostWasDeleted;
use App\Events\PostWasModified;

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
	
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// When creating a post, make sure it has a board_id.
		static::creating(function($post)
		{
			return isset($post->board_id);
		});
		
		// Fire events on post created.
		static::created(function(Post $post) {
			Event::fire(new PostWasAdded($post));
		});
		
		// When deleting a post, delete its children.
		static::deleting(function($post)
		{
			static::replyTo($post->post_id)->delete();
		});
		
		// After a post is deleted, update OP's reply count.
		static::deleted(function($post) {
			if (!is_null($post->reply_to))
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
			
			Event::fire(new PostWasDeleted($post));
		});
		
		// Fire events on post updated.
		static::updated(function(Post $post) {
			Event::fire(new PostWasModified($post));
		});
		
	}
	
	/**
	 * Determines if the user can edit this post.
	 *
	 * @param  App\User|App\Support\Anonymous  $user
	 * @return boolean
	 */
	public function canEdit($user)
	{
		return $user->canEdit($this);
	}
	
	/**
	 * Determines if the user can delete this post.
	 *
	 * @param  App\User|App\Support\Anonymous  $user
	 * @return boolean
	 */
	public function canDelete($user)
	{
		return $user->canDelete($this);
	}
	
	/**
	 * Determines if the user can edit this post, or if this thread is open to replies in general.
	 *
	 * @param  App\User|App\Support\Anonymous|null  $user
	 * @return boolean
	 */
	public function canReply($user = null)
	{
		if (!is_null($user))
		{
			return $user->canReply($this);
		}
		
		return true;
	}
	
	/**
	 * Determines if the user can sticky or unsticky this post.
	 *
	 * @param  App\User|App\Support\Anonymous  $user
	 * @return boolean
	 */
	public function canSticky($user)
	{
		return $user->canSticky($this);
	}
	
	
	/**
	 * Returns the fully rendered HTML content of this post.
	 *
	 * @return string
	 */
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
	
	
	/**
	 * Determines if this is a bumpless post.
	 *
	 * @return boolean
	 */
	public function isBumpless()
	{
		if ($this->email == "sage")
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the board model for this post.
	 *
	 * @return \App\Board
	 */
	public function getBoard()
	{
		return $this->board()
			->get()
			->first();
	}
	
	/**
	 * Returns the post model using the board's URI and the post's local board ID.
	 *
	 * @param  string  $board_uri
	 * @param  integer  $board_id
	 * @return \App\Post
	 */
	public static function getPostForBoard($board_uri, $board_id)
	{
		return static::where([
				'board_uri' => $board_uri,
				'board_id' => $board_id,
			])
			->first();
	}
	
	/**
	 * Returns the model for this post's original post (what it is a reply to).
	 *
	 * @return \App\Post
	 */
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
	
	/**
	 * Returns all replies to a post.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getReplies()
	{
		if (isset($this->replies))
		{
			return $this->replies;
		}
		
		return $this->replies()
			->withEverything()
			->get();
	}
	
	/**
	 * Returns the last few replies to a thread for index views.
	 *
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getRepliesForIndex()
	{
		return $this->replies()
			->forIndex()
			->get()
			->reverse();
	}
	
	/**
	 * Sets the sticky property of a post and updates relevant timestamps.
	 *
	 * @param  bolean  $sticky
	 * @return \App\Post
	 */
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
	
	
	public function scopeAndAttachments($query)
	{
		return $query->with('attachments');
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
				'roles.role as capcode_role',
				'roles.name as capcode_name'
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
	
	public function scopeAndReplies($query)
	{
		return $query->with(['replies' => function($query) {
			$query->withEverything();
		}]);
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
		return $query->withEverything()
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
	
	public function scopeWithEverything($query)
	{
		return $query->visible()
			->andAttachments()
			->andBan()
			->andCapcode()
			->andEditor();
	}
	
	public function scopeWithEverythingAndReplies($query)
	{
		return $query->op()
			->withEverything()
			->with(['replies' => function($query) {
				$query->withEverything();
			}]);
	}
	
	
	/**
	 * Pushes the post to the specified board, as a new thread or as a reply.
	 * This autoatically handles concurrency issues. Creating a new reply without
	 * using this method is forbidden by the `creating` event in ::boot.
	 *
	 *
	 * @param  App\Board  &$board
	 * @param  App\Post   &$thread
	 * @return void
	 */
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
		DB::transaction(function() use ($thread)
		{
			// The objective of this transaction is to prevent concurrency issues in the database
			// on the unique joint index [`board_uri`,`board_id`] which is generated procedurally
			// alongside the primary autoincrement column `post_id`.
			
			// First instruction is to add +1 to posts_total.
			DB::table('boards')
				->where('board_uri', $this->board_uri)
				->increment('posts_total');
			
			// Second, we record this value and lock the table.
			$boards = DB::table('boards')
				->where('board_uri', $this->board_uri)
				->lockForUpdate()
				->select('posts_total')
				->get();
			
			$posts_total = $boards[0]->posts_total;
			
			// Optionally, the OP of this thread needs a +1 to reply count.
			if ($thread instanceof Post)
			{
				if (!$this->isBumpless())
				{
					$thread->reply_last  = $this->created_at;
				}
				
				$thread->reply_count += 1;
				$thread->save();
			}
			
			// Finally, we set our board_id and save.
			$this->board_id = $posts_total;
			$this->save();
			
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
