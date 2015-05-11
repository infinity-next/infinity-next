<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class FileStorage extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'files';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['hash', 'banned', 'filesize', 'first_uploaded_at', 'last_uploaded_at', 'upload_count'];
	
	public $timestamps = false;
	
	
	public function attachments()
	{
		return $this->hasMany('\App\FileAttachment', 'post', 'id');
	}
	
	
	public static function getHash($hash)
	{
		return static::hash($hash)->get()->first();
	}
	
	
	public function scopeHash($query, $hash)
	{
		return $query->where('hash', $hash);
	}
}
