<?php namespace App;

use App\FileStorage;
use App\FileAttachment;
use App\PostCite;
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
use App\Events\ThreadNewReply;

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
	protected $fillable = [
		'board_uri',
		'board_id',
		'reply_to',
		'reply_to_board_id',
		'reply_last',
		'bumped_last',
		
		'stickied',
		'stickied_at',
		'bumplocked_at',
		'locked_at',
		
		'author_ip',
		'capcode_id',
		'subject',
		'author',
		'email',
		'body',
	];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['author_ip'];
	
	/**
	 * Attributes which are automatically sent through a Carbon instance on load.
	 *
	 * @var array
	 */
	protected $dates = ['reply_last', 'bumped_last', 'created_at', 'updated_at', 'deleted_at', 'stickied_at', 'bumplocked_at', 'locked_at'];
	
	
	
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
	
	public function cites()
	{
		return $this->hasMany('\App\PostCite', 'post_id');
	}
	
	public function citedBy()
	{
		return $this->hasMany('\App\PostCite', 'cite_id', 'post_id');
	}
	
	public function citedPosts()
	{
		return $this->belongsToMany("\App\Post", 'post_cites', 'post_id');
	}
	
	public function citedByPosts()
	{
		return $this->belongsToMany("\App\Post", 'post_cites', 'cite_id', 'post_id');
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
		
		// Update citation references
		static::saved(function(Post $post)
		{
			$post->cites()->delete();
			
			// Process citations.
			$cited = $post->getCitesFromText();
			$cites = [];
			
			foreach ($cited['posts'] as $citedPost)
			{
				$cites[] = new PostCite([
					'post_board_uri' => $post->board_uri,
					'post_board_id'  => $post->board_id,
					'cite_id'        => $citedPost->post_id,
					'cite_board_uri' => $citedPost->board_uri,
					'cite_board_id'  => $citedPost->board_id,
				]);
			}
			
			foreach ($cited['boards'] as $citedBoard)
			{
				$cites[] = new PostCite([
					'post_board_uri' => $post->board_uri,
					'cite_board_uri' => $citedBoard->board_uri,
				]);
			}
			
			if (count($cites) > 0)
			{
				$post->cites()->saveMany($cites);
			}
			
		});
		
		// Fire events on post updated.
		static::updated(function(Post $post)
		{
			if ($post->isDirty(['subject', 'author', 'email', 'body']))
			{
				Event::fire(new PostWasModified($post));
			}
		});
		
	}
	
	/**
	 * Determines if the user can bumplock this post
	 *
	 * @param  App\User|App\Support\Anonymous  $user
	 * @return boolean
	 */
	public function canBumplock($user)
	{
		if (!$this->bumplocked_at)
		{
			return $user->canBumplock($this);
		}
		
		return false;
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
	 * Determines if the user can lock this post
	 *
	 * @param  App\User|App\Support\Anonymous  $user
	 * @return boolean
	 */
	public function canLock($user)
	{
		if (!$this->locked_at)
		{
			return $user->canLock($this);
		}
		
		return false;
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
		if (!$this->stickied_at)
		{
			return $user->canSticky($this);
		}
		
		return false;
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
	
	/**
	 * Returns a relative URL for opening this post.
	 *
	 * @return string
	 */
	public function getURL()
	{
		$url = "/{$this->board_uri}/thread/";
		
		if ($this->reply_to_board_id)
		{
			$url .= "{$this->reply_to_board_id}#{$this->board_id}";
		}
		else
		{
			$url .= "{$this->board_id}";
		}
		
		return $url;
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
	 * Determines if this thread cannot be bumped.
	 *
	 * @return boolean
	 */
	public function isBumplocked()
	{
		return !is_null($this->bumplocked_at);
	}
	
	/**
	 * Determines if this is deleted.
	 *
	 * @return boolean
	 */
	public function isDeleted()
	{
		return !is_null($this->deleted_at);
	}
	
	/**
	 * Determines if this is the first reply in a thread.
	 *
	 * @return boolean
	 */
	public function isOp()
	{
		return is_null($this->reply_to);
	}
	
	/**
	 * Determines if this thread is locked.
	 *
	 * @return boolean
	 */
	public function isLocked()
	{
		return !is_null($this->locked_at);
	}
	
	/**
	 * Determines if this thread is stickied.
	 *
	 * @return boolean
	 */
	public function isStickied()
	{
		return !is_null($this->stickied_at);
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
	 * Parses the post text for citations.
	 *
	 * @return Collection
	 */
	public function getCitesFromText()
	{
		return ContentFormatter::getCites($this);
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
	 * Returns the last post made by this user across the entire site.
	 *
	 * @param  string $ip
	 * @return \App\Post
	 */
	public static function getLastPostForIP($ip = null)
	{
		if (is_null($ip))
		{
			$ip = Request::getClientIp();
		}
		
		return Post::where('author_ip', $ip)
			->orderBy('created_at', 'desc')
			->take(1)
			->get()
			->first();
	}
	
	/**
	 * Returns the page on which this thread appears.
	 * If the post is a reply, it will return the page it appears on in the thread, which is always 1.
	 *
	 * @return \App\Post
	 */
	public function getPage()
	{
		if ($this->isOp())
		{
			$board          = $this->board()->with('settings')->get()->first();
			$visibleThreads = $board->threads()->op()->visible()->where('bumped_last', '>=', $this->bumped_last)->count();
			$threadsPerPage = (int) $board->getSetting('postsPerPage', 10);
			
			return floor(($visibleThreads - 1) / $threadsPerPage) + 1;
		}
		
		return 1;
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
	 * Sets the bumplock property timestamp.
	 *
	 * @param  boolean  $bumplock
	 * @return \App\Post
	 */
	public function setBumplock($bumplock = true)
	{
		if ($bumplock)
		{
			$this->bumplocked_at = $this->freshTimestamp();
		}
		else
		{
			$this->bumplocked_at = null;
		}
		
		return $this;
	}
	
	/**
	 * Sets the deleted timestamp.
	 *
	 * @param  boolean  $delete
	 * @return \App\Post
	 */
	public function setDeleted($delete = true)
	{
		if ($delete)
		{
			$this->deleted_at = $this->freshTimestamp();
		}
		else
		{
			$this->deleted_at = null;
		}
		
		return $this;
	}
	
	/**
	 * Sets the locked property timestamp.
	 *
	 * @param  boolean  $lock
	 * @return \App\Post
	 */
	public function setLock($lock = true)
	{
		if ($lock)
		{
			$this->locked_at = $this->freshTimestamp();
		}
		else
		{
			$this->locked_at = null;
		}
		
		return $this;
	}
	
	/**
	 * Sets the sticky property of a post and updates relevant timestamps.
	 *
	 * @param  boolean  $sticky
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
	
	public function scopeAndFirstAttachment($query)
	{
		return $query->with(['attachments' => function($query)
		{
			$query->limit(1);
		}]);
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
	
	public function scopeAndCites($query)
	{
		return $query->with('cites', 'cites.cite');
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
			->orderBy('post_id', 'desc');
			//->take( $this->stickied_at ? 1 : 5 );
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
			->andCites()
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
		$this->board_uri   = $board->board_uri;
		$this->author_ip   = Request::getClientIp();
		$this->reply_last  = $this->freshTimestamp();
		$this->bumped_last = $this->reply_last;
		$this->setCreatedAt($this->reply_last);
		$this->setUpdatedAt($this->reply_last);
		
		if (!is_null($thread) && !($thread instanceof Post))
		{
			$thread = $board->getLocalThread($thread);
			$this->reply_to = $thread->post_id;
			$this->reply_to_board_id = $thread->board_id;
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
				if (!$this->isBumpless() && !$thread->isBumplocked())
				{
					$thread->bumped_last = $this->created_at;
				}
				
				$thread->reply_last  = $this->created_at;
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
				if(file_exists($upload->getPathname()))
				{
					$uploadName  = urlencode($upload->getClientOriginalName());
					$uploadExt   = pathinfo($uploadName, PATHINFO_EXTENSION);
					
					$fileName    = basename($uploadName, "." . $uploadExt);
					$fileExt     = $upload->guessExtension();
					
					$storage     = FileStorage::storeUpload($upload);
					
					$attachment  = new FileAttachment();
					$attachment->post_id  = $this->post_id;
					$attachment->file_id  = $storage->file_id;
					$attachment->filename = urlencode("{$fileName}.{$fileExt}");
					$attachment->save();
				}
			}
		}
		
		
		// Finally fire event on OP, if it exists.
		if ($thread instanceof Post)
		{
			Event::fire(new ThreadNewReply($thread));
		}
	}
	
}
