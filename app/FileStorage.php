<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use File;
use Storage;

class FileStorage extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'files';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'file_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['hash', 'banned', 'filesize', 'first_uploaded_at', 'last_uploaded_at', 'upload_count'];
	
	public $timestamps = false;
	
	
	public function posts()
	{
		return $this->belongsToMany("\App\Post", 'file_attachments', 'file_id', 'post_id')->withPivot('filename');
	}
	
	
	public static function getHash($hash)
	{
		return static::hash($hash)->get()->first();
	}
	
	public static function getHashPrefix($hash)
	{
		return implode(str_split(substr($hash, 0, 4)), "/");
	}
	
	public function getDirectory()
	{
		$prefix = $this->getHashPrefix($this->hash);
		
		return "attachments/full/{$prefix}";
	}
	
	public function getDirectoryThumb()
	{
		$prefix = $this->getHashPrefix($this->hash);
		
		return "attachments/thumb/{$prefix}";
	}
	
	public function getAsFile()
	{
		return new File($this->getFullPath());
	}
	
	public function getAsFileThumb()
	{
		return new File($this->getFullPathThumb());
	}
	
	public function getFullPath()
	{
		return storage_path() . "/app/" . $this->getPath();
	}
	
	public function getFullPathThumb()
	{
		return storage_path() . "/app/" . $this->getPathThumb();
	}
	
	public function getPath()
	{
		return $this->getDirectory() . "/" . $this->hash;
	}
	
	public function getPathThumb()
	{
		return $this->getDirectoryThumb() . "/" . $this->hash;
	}
	
	public function scopeHash($query, $hash)
	{
		return $query->where('hash', $hash);
	}
	
	/**
	 * A dumb way to guess the file type based on the mime
	 * 
	 * @return string
	 */
	
	public function guessExtension()
	{
		$mimes = explode("/", $this->mime);
		
		switch ($this->mime)
		{
			case "image/jpeg" :
			case "image/jpg" :
				return "jpg";
			
			case "image/gif" :
				return "gif";
			
			case "image/png" :
				return "png";
			
			case "text/plain" :
				return "txt";
		}
		
		return $mimes[1];
	}
	
	/**
	 * Supplies a clean URL for downloading an attachment on a board.
	 *
	 * @param  App\Board  $board
	 * @return string
	 */
	public function getDownloadURL(Board $board)
	{
		if (isset($this->pivot) && isset($this->pivot->filename))
		{
			return url("{$board->board_uri}/file/{$this->hash}/") . "/" . $this->pivot->filename;
		}
		else
		{
			return url("{$board->board_uri}/file/{$this->hash}/") . "/" . strtotime($this->first_uploaded_at) . "." . $this->guessExtension();
		}
	}
	
	/**
	 * Handles an UploadedFile from form input. Stores, creates a model, and generates a thumbnail.
	 *
	 * @param UploadedFile $upload
	 * @return FileStorage
	 */
	public static function storeUpload(UploadedFile $upload)
	{
		$fileContent  = File::get($upload);
		$fileMD5      = md5(File::get($upload));
		$storage      = static::getHash($fileMD5);
		
		if (!($storage instanceof static))
		{
			$storage           = new static();
			$fileTime          = $storage->freshTimestamp();
			
			$storage->hash     = $fileMD5;
			$storage->banned   = false;
			$storage->filesize = $upload->getSize();
			$storage->mime     = $upload->getClientMimeType();
			$storage->first_uploaded_at = $fileTime;
			$storage->upload_count = 0;
		}
		else
		{
			$fileTime = $storage->freshTimestamp();
		}
		
		$storage->last_uploaded_at = $fileTime;
		$storage->upload_count += 1;
		$storage->save();
		
		if (!Storage::exists($storage->getPath()))
		{
			Storage::put($storage->getPath(), $fileContent);
			Storage::makeDirectory($storage->getDirectoryThumb());
			
			$imageManager = new ImageManager;
			$imageManager
				->make($storage->getFullPath())
				->resize(
					## TODO ##
					// Add a way for options to be recovered without a controller.
					300,//$controller->option('attachmentThumbnailSize'),
					300,//$controller->option('attachmentThumbnailSize'),
					function($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					}
				)
				->save($storage->getFullPathThumb());
		}
		
		return $storage;
	}
}
