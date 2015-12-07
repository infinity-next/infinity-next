<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class FileAttachment extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'file_attachments';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['post_id', 'file_id', 'filename', 'is_spoiler', 'position'];
	
	/**
	 * Indicates if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var array
	 */
	public $incrementing = false;
	
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public function storage()
	{
		return $this->belongsTo('\App\FileStorage', 'file_id');
	}
	
	
	/**
	 * Ties database triggers to the model.
	 *
	 * @static
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		
		// Fire events on post created.
		static::created(function(FileAttachment $attachment) {
			$attachment->storage->processAttachment($attachment);
		});
	}
	
	/**
	 * Returns a few posts for the front page.
	 *
	 * @static
	 * @param  int  $number  How many to pull.
	 * @param  boolean $sfwOnly  If we only want SFW boards.
	 * @return Collection  of static
	 */
	public static function getRecentImages($number = 16, $sfwOnly = true)
	{
		$query = static::where('is_spoiler', false)
			->whereHas('storage', function($query) {
				$query->where('has_thumbnail', true);
			})
			->whereHas('post.board', function($query) use ($sfwOnly) {
				$query->where('is_indexed', true);
				$query->where('is_overboard', true);
				
				if ($sfwOnly)
				{
					$query->where('is_worksafe', '=', true);
				}
			})
			->with('storage')
			->with('post.board')
			->take($number);
		
		if ($query->getQuery()->getConnection() instanceof \Illuminate\Database\PostgresConnection)
		{
			// PostgreSQL does not support the MySQL standards non-compliant group_by syntax.
			// DISTINCT itself selects distinct combinations [attachment_id,file_idd, not just file_id.
			// We have to use raw SQL to accomplish this.
			$query->select(
				\DB::raw("DISTINCT ON (file_id) *")
			);
			
			$query->orderBy('file_id', 'desc');
		}
		else
		{
			$query->orderBy('attachment_id', 'desc');
			$query->groupBy('file_id');
		}
		
		return $query->get();
	}
}
