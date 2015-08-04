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
	
	public function attachments()
	{
		return $this->hasMany('\App\FileAttachment', 'file_id');
	}
	
	public function assets()
	{
		return $this->hasMany('\App\BoardAsset', 'file_id');
	}
	
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
	
	/**
	 * Converts the byte size to a human-readable filesize.
	 *
	 * @author Jeffrey Sambells
	 * @param  int  $decimals
	 * @return string
	 */
	public function getHumanFilesize($decimals = 2)
	{
		$bytes  = $this->filesize;
		$size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$factor = floor((strlen($bytes) - 1) / 3);
		
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$size[$factor];
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
	 * Will trigger a file deletion if the storage item is not used anywhere.
	 *
	 * @return boolean
	 */
	public function challengeExistence()
	{
		$count = $this->assets->count() + $this->attachments->count();
		
		if ($count === 0)
		{
			$this->forceDelete();
			return false;
		}
		
		return true;
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
			##
			# IMAGES
			##
			case "image/svg+xml" :
				return "svg";
			
			case "image/jpeg" :
			case "image/jpg" :
				return "jpg";
			
			case "image/gif" :
				return "gif";
			
			case "image/png" :
				return "png";
			
			##
			# TEXT DOCUMENTS
			##
			case "text/plain" :
				return "txt";
			
			##
			# MULTIMEDIA
			##
			case "application/epub+zip" :
				return "epub";
			
			case "application/pdf" :
				return "pdf";
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
			return url("/{$board->board_uri}/file/{$this->hash}/") . "/" . $this->pivot->filename;
		}
		else
		{
			return url("/{$board->board_uri}/file/{$this->hash}/") . "/" . strtotime($this->first_uploaded_at) . "." . $this->guessExtension();
		}
	}
	
	/**
	 * Returns an XML valid attachment HTML string that handles missing thumbnail URLs.
	 *
	 * @return string as HTML
	 */
	public function getThumbnailHTML(Board $board)
	{
		$ext = $this->guessExtension();
		$url = "/img/filetypes/{$ext}.svg";
		
		switch ($ext)
		{
			case "svg" :
			case "jpg" :
			case "png" :
			case "gif" :
				$url = $this->getThumbnailURL($board);
				break;
		}
		
		return "<img class=\"attachment-img\" src=\"{$url}\" />";
	}
	
	/**
	 * Supplies a clean thumbnail URL for embedding an attachment on a board.
	 *
	 * @param  App\Board  $board
	 * @return string
	 */
	public function getThumbnailURL(Board $board)
	{
		$baseURL = "/{$board->board_uri}/file/thumb/{$this->hash}/";
		
		if ($this->guessExtension() === "svg")
		{
			// With the SVG filetype, we do not generate a thumbnail, so just serve the actual SVG.
			$baseURL ="/{$board->board_uri}/file/{$this->hash}/";
		}
		
		if (isset($this->pivot) && isset($this->pivot->filename))
		{
			return url($baseURL . $this->pivot->filename);
		}
		else
		{
			return url($baseURL . strtotime($this->first_uploaded_at) . "." . $this->guessExtension());
		}
	}
	
	/**
	 * Turns an image into a thumbnail if possible, overwriting previous versions.
	 *
	 * @return void
	 */
	protected function processThumb()
	{
		switch ($this->guessExtension())
		{
			case "jpg" :
			case "gif" :
			case "png" :
				if (!Storage::exists($this->getFullPathThumb()))
				{
					Storage::makeDirectory($this->getDirectoryThumb());
					
					$imageManager = new ImageManager;
					$imageManager
						->make($this->getFullPath())
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
						->save($this->getFullPathThumb());
				}
			break;
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
		}
		
		$storage->processThumb();
		
		return $storage;
	}
}
