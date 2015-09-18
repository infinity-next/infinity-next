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
	protected $fillable = ['hash', 'banned', 'filesize', 'mime', 'meta', 'first_uploaded_at', 'last_uploaded_at', 'upload_count'];
	
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
	
	public function isSpoiler()
	{
		return isset($this->pivot) && isset($this->pivot->is_spoiler) && !!$this->pivot->is_spoiler;
	}
	
	public function hasThumb()
	{
		return file_exists($this->getFullPathThumb());
	}
	
	public function scopeWhereCanDelete($query)
	{
		return $query->where('banned', false);
	}
	
	public function scopeWhereOprhan($query)
	{
		return $query->whereCanDelete()
			->has('assets',      '=', 0)
			->has('attachments', '=', 0);
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
			# AUDIO
			##
			case "audio/mp3" :
				return "mp3";
			
			case "audio/ogg" :
				return "ogg";
			
			case "audio/wav" :
				return "wav";
			
			case "audio/mpeg" :
				return "mpga";
			
			##
			# MULTIMEDIA
			##
			case "video/webm" :
				return "webm";
			
			case "video/mp4" :
				return "mp4";
			
			case "video/ogg" :
				return "ogg";
			
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
		$ext   = $this->guessExtension();
		$mime  = $this->mime;
		$url   = "/img/filetypes/{$ext}.svg";
		$type  = "other";
		$html  = "";
		$stock = true;
		$spoil = $this->isSpoiler();
		
		switch ($ext)
		{
			case "mp3"  :
			case "mpga" :
				$stock = false;
				$type  = "audio";
				$url   = $this->getThumbnailURL($board);
				break;
			
			case "mp4"  :
			case "webm" :
				if ($this->hasThumb())
				{
					$stock = false;
					$url   = $this->getThumbnailURL($board);
					$type  = "video";
				}
				break;
			
			case "svg"  :
				$stock = false;
				$url   = $this->getDownloadURL($board);
				$type  = "img";
				break;
			
			case "jpg"  :
			case "png"  :
			case "gif"  :
				if ($this->hasThumb())
				{
					$stock = false;
					$url   = $this->getThumbnailURL($board);
					$type  = "img";
				}
				break;
		}
		
		$classes = [];
		$classes['type']  = "attachment-type-{$type}";
		$classes['ext']   = "attachent-ext-{$ext}";
		$classes['stock'] = $stock ? "thumbnail-stock" : "thumbnail-content";
		$classes['spoil'] = $spoil ? "thumbnail-spoiler" : "thumbnail-not-spoiler";
		$classHTML = implode(" ", $classes);
		
		return "<div class=\"attachment-wrapper {$classHTML}\"><img class=\"attachment-img {$classHTML}\" src=\"{$url}\" data-mime=\"{$mime}\" /></div>";
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
		$ext     = $this->guessExtension();
		
		if ($this->isSpoiler())
		{
			return $board->getSpoilerUrl();
		}
		
		switch ($ext)
		{
			case "mp3"  :
			case "mpga" :
				if (!$this->hasThumb())
				{
					return $board->getAudioArtURL();
				}
				break;
			
			case "svg" :
				// With the SVG filetype, we do not generate a thumbnail, so just serve the actual SVG.
				$baseURL ="/{$board->board_uri}/file/{$this->hash}/";
				break;
		}
		
		// Sometimes we supply a filename when fetching the filestorage as an attachment.
		if (isset($this->pivot) && isset($this->pivot->filename))
		{
			return url($baseURL . urlencode($this->pivot->filename));
		}
		
		return url($baseURL . strtotime($this->first_uploaded_at) . "." . $this->guessExtension());
	}
	
	/**
	 * Is this attachment an image?
	 *
	 * @return boolean
	 */
	public function isImage()
	{
		switch ($this->guessExtension())
		{
			case "bmp"  :
			case "jpeg" :
			case "jpg"  :
			case "gif"  :
			case "png"  :
				return true;
		}
		
		return false;
	}
	
	/**
	 * Is this attachment a video?
	 * Primarily used to split files on HTTP range requests.
	 *
	 * @return boolean
	 */
	public function isVideo()
	{
		switch ($this->guessExtension())
		{
			case "mp4"  :
			case "webm" :
				return true;
		}
		
		return false;
	}
	
	
	/**
	 * Creates a new FileAttachment for a post.
	 *
	 * @param  UploadedFile  $file
	 * @param  Post  $post
	 * @return FileAttachment
	 */
	public static function createAttachment(UploadedFile $file, Post $post)
	{
		$storage     = static::storeUpload($file);
		
		$uploadName  = urlencode($file->getClientOriginalName());
		$uploadExt   = pathinfo($uploadName, PATHINFO_EXTENSION);
		
		$fileName    = basename($uploadName, "." . $uploadExt);
		$fileExt     = $storage->guessExtension();
		
		$attachment  = new FileAttachment();
		$attachment->post_id  = $post->post_id;
		$attachment->file_id  = $storage->file_id;
		$attachment->filename = urlencode("{$fileName}.{$fileExt}");
		$attachment->save();
		
		return $attachment;
	}
	
	/**
	 * Collects data from an UploadFile type and stores it.
	 *
	 * @return int
	 */
	public static function probe(UploadedFile &$file)
	{
		$video = $file->getPathname();
		$cmd   = env('LIB_VIDEO_PROBE', "ffprobe") . " -v error -show_format -show_streams {$video} 2>&1";
		
		exec($cmd, $output, $returnvalue);
		
		if (count($output) <= 3)
		{
			foreach ($output as $line)
			{
				$line = (string) $line;
				
				if (strlen($line) > 0 && (stripos($line, 'invalid') !== false || stripos($line, 'error') !== false))
				{
					dd($output);
					return false;
				}
			}
		}
		
		// Hack.
		// Appends this output so we don't need to run twice.
		$file->ffmpegData = $output;
		
		return $returnvalue !== 1;
	}
	
	/**
	 * Turns an image into a thumbnail if possible, overwriting previous versions.
	 *
	 * @return void
	 */
	protected function processThumb()
	{
		global $app;
		
		switch ($this->guessExtension())
		{
			case "mp3"  :
			case "mpga" :
			case "wav"  :
				if (!Storage::exists($this->getFullPathThumb()))
				{
					$ID3  = new \getID3();
					$meta = $ID3->analyze($this->getFullPath());
					
					if (isset($meta['comments']['picture']) && count($meta['comments']['picture']))
					{
						foreach ($meta['comments']['picture'] as $albumArt)
						{
							try
							{
								$imageManager = new ImageManager;
								$imageManager
									->make($albumArt['data'])
									->resize(
										$app['settings']('attachmentThumbnailSize'),
										$app['settings']('attachmentThumbnailSize'),
										function($constraint) {
											$constraint->aspectRatio();
											$constraint->upsize();
										}
									)
									->save($this->getFullPathThumb());
							}
							catch (\Exception $error)
							{
								// Nothing.
							}
							
							break;
						}
					}
				}
				break;
			
			case "flv"  :
			case "mp4"  :
			case "webm" :
				if (!Storage::exists($this->getFullPathThumb()))
				{
					Storage::makeDirectory($this->getDirectoryThumb());
					
					$video    = $this->getFullPath();
					$image    = $this->getFullPathThumb();
					$interval = 0;
					$frames   = 1;
					
					$cmd = env('LIB_VIDEO', "ffmpeg") . " -i $video -deinterlace -an -ss $interval -f mjpeg -t 1 -r 1 -y $image 2>&1";
					
					exec($cmd, $output, $returnvalue);
					
					// Constrain thumbnail to proper dimensions.
					if (Storage::exists($image))
					{
						$imageManager = new ImageManager;
						$imageManager
							->make($this->getFullPath())
							->resize(
								$app['settings']('attachmentThumbnailSize'),
								$app['settings']('attachmentThumbnailSize'),
								function($constraint) {
									$constraint->aspectRatio();
									$constraint->upsize();
								}
							)
							->save($this->getFullPathThumb());
					}
				}
				break;
			
			case "jpg"  :
			case "gif"  :
			case "png"  :
				if (!Storage::exists($this->getFullPathThumb()))
				{
					Storage::makeDirectory($this->getDirectoryThumb());
					
					$imageManager = new ImageManager;
					$imageManager
						->make($this->getFullPath())
						->resize(
							$app['settings']('attachmentThumbnailSize'),
							$app['settings']('attachmentThumbnailSize'),
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
	 * @param  UploadedFile  $upload
	 * @return FileStorage
	 */
	public static function storeUpload(UploadedFile $upload)
	{
		$fileContent  = File::get($upload);
		$fileMD5      = md5((string) File::get($upload));
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
			
			if (isset($upload->ffmpegData))
			{
				$meta = [];
				$codecType = null;
				$codecName = null;
				
				foreach ($upload->ffmpegData as $datum)
				{
					$datumItems = explode("=", $datum, 2);
					
					if (count($datumItems) == 2)
					{
						$datumValue = $datumItems[1];
						
						switch ($datumItems[0])
						{
							case "codec_name" :
								$codecName = $datumItems[1];
								break;
							
							case "codec_type" :
								$codecType = $datumItems[1];
								break;
							
							default :
								$datumKeys  = explode(":", $datumItems[0], 2);
								
								if (count($datumKeys) == 2)
								{
									if($datumKeys[0] === "TAG")
									{
										$meta[$datumKeys[1]] = $datumItems[1];
									}
								}
								break;
						}
					}
				}
				
				if (!is_null($codecType) && !is_null($codecName))
				{
					$storage->mime = "{$codecType}/{$codecName}";
				}
				
				if (count($meta))
				{
					$storage->meta = json_encode($meta);
				}
			}
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
