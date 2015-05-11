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
	protected $fillable = ['post', 'file', 'filename'];
	
	public $timestamps = false;
	
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post', 'id');
	}
	
	public function storage()
	{
		return $this->belongsTo('\App\FileStorage', 'file', 'id');
	}
}
