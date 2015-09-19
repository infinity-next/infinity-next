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
	protected $fillable = ['post_id', 'file_id', 'filename', 'is_spoiler'];
	
	public $timestamps = false;
	
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public function storage()
	{
		return $this->belongsTo('\App\FileStorage', 'file_id');
	}
	
	
	public static function fetchRecentImages()
	{
		return static::orderBy('attachment_id', 'desc')
			->whereHas('storage', function($query) {
				$query->where('has_thumbnail', '=', true);
			})
			->has('post.board')
			->with('storage')
			->with('post.board')
			->groupBy('file_id')
			->limit(20)
			->get();
	}
	
}
