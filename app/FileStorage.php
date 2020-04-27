<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use File;
use Request;
use Settings;
use Storage;

/**
 * Model representing files in our storage system.
 *
 * Can represent attachments and is used for content renering on posts.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class FileStorage extends Model
{
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
    protected $fillable = [
        'source_id',
        'hash',
        'phash',
        'filesize',
        'file_width',
        'file_height',
        'mime',
        'meta',
        'banned_at',
        'fuzzybanned_at',
        'first_uploaded_at',
        'last_uploaded_at',
        'upload_count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'banned_at' => "datetime",
        'fuzzybanned_at' => "datetime",
        'first_uploaded_at' => "datetime",
        'last_uploaded_at' => "datetime",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'phash',
        'banned_at',
        'fuzzybanned_at',
        'first_uploaded_at',
        'last_uploaded_at',
        'upload_count',
    ];

    /**
     * Determines if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Blob data for the file this object represents.
     *
     *
     * @var string
     */
     public $blob;

     /**
      * Cheeky static to store default Settings::get('attachmentName') after first call
      *
      * @var string
      */
     public static $nameFormat;

    /**
     * The \App\BoardAsset relationship.
     * Used for multiple custom facets of a board.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(BoardAsset::class, 'file_id');
    }

    /**
     * The \App\Posts relationship.
     * Uses the postAttachments() relationship to find posts where this file is attached..
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_attachments', 'file_id', 'post_id')
            ->withPivot('filename', 'position');
    }

    /**
     * The \App\PostAttachment relationship.
     * Represents a post -> storage relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function postAttachments()
    {
        return $this->hasMany(PostAttachment::class, 'file_id');
    }

    public function sources()
    {
        return $this->belongsToMany(static::class, 'file_thumbnails', 'thumbnail_id', 'source_id');
    }

    public function sourcePivots()
    {
        return $this->hasMany(FileThumbnail::class, 'source_id', 'file_id');
    }

    public function thumbnails()
    {
        return $this->belongsToMany(static::class, 'file_thumbnails', 'source_id', 'thumbnail_id');
    }

    public function thumbnailPivots()
    {
        return $this->hasMany(FileThumbnail::class, 'thumbnail_id', 'file_id');
    }

    /**
     * Will trigger a file deletion if the storage item is not used anywhere.
     *
     * @return bool
     */
    public function challengeExistence()
    {
        $count = $this->assets->count() + $this->postAttachments->count();

        if ($count === 0) {
            $this->deleteFile();

            return false;
        }

        return true;
    }

    public static function checkUploadExists(UploadedFile $file, Board $board, Post $thread = null)
    {
        $hash = hash('sha256', (string) File::get($file));
        return static::checkHashExists($hash, $board, $thread);
    }

    public static function checkHashExists($hash, Board $board, Post $thread = null)
    {
        $storage = static::whereHash($hash)->first();
        if (!$storage) {
            return;
        }

        $query = Post::where('board_uri', $board->board_uri);

        if (!is_null($thread)) {
            $query = $query->whereInThread($thread);
        }

        return $query->whereHas('attachments', function ($subQuery) use ($storage) {
            $subQuery->where('file_id', $storage->file_id);
        })->first();
    }

    /**
     * Creates a new PostAttachment for a post using a direct upload.
     *
     * @param  UploadedFile  $file
     * @param  Post          $post
     *
     * @return PostAttachment
     */
    public static function createAttachmentFromUpload(UploadedFile $file, Post $post, $autosave = true)
    {
        ## TODO ##  This needs to be moved somewhere elss stupid.
        $storage = static::storeUpload($file);

        $uploadName = urlencode($file->getClientOriginalName());
        $uploadExt = pathinfo($uploadName, PATHINFO_EXTENSION);

        $fileName = basename($uploadName, '.'.$uploadExt);
        $fileExt = $storage->guessExtension();

        $attachment = new PostAttachment;
        $attachment->post_id = $post->post_id;
        $attachment->file_id = $storage->file_id;
        $attachment->filename = urlencode("{$fileName}.{$fileExt}");
        $attachment->is_spoiler = (bool) Request::input('spoilers');

        if ($autosave) {
            $attachment->save();

            ++$storage->upload_count;
            $storage->save();
        }

        return $attachment;
    }

    /**
     * Removes the associated file for this storage.
     *
     * @return bool Success. Will return FALSE if the file was already gone.
     */
    public function deleteFile()
    {
        return @unlink($this->getFullPath());
    }

    /**
     * Returns the storage's file as a filesystem.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getAsFile()
    {
        return new File($this->getFullPath());
    }

    /**
     * Returns the storage directory, minus the file name.
     *
     * @return string
     */
    public function getDirectory()
    {
        $prefix = $this->getHashPrefix($this->hash);

        return "attachments/full/{$prefix}";
    }

    /**
     * Supplies a clean URL for downloading an attachment on a board.
     *
     * @param App\Board $board
     *
     * @return string
     */
    public function getUrl(?Board $board = null)
    {
        $params = [
            'hash' => $this->hash,
            'filename' => "{$this->file_id}.{$this->guessExtension()}"
        ];

        if (is_null($board)) {
            return route('panel.site.files.send', $params);
        }
        elseif (!config('app.url_media', false)) {
            $params['board'] = $board;
        }

        return route('static.file.hash', $params, config('app.url_media', false));
    }

    /**
     * Returns the attachment's extension.
     *
     * @return string
     */
    public function getExtension()
    {
        $pathinfo = pathinfo($this->pivot->filename);

        return $pathinfo['extension'];
    }

    /**
     * Returns the dimensions of the thumbnail, if possible.
     *
     * @return string|null
     */
    public function getFileDimensions()
    {
        return "{$this->attributes['file_width']}x{$this->attributes['file_height']}";
    }

    /**
     * Returns the full internal file path.
     *
     * @return string
     */
    public function getFullPath()
    {
        $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();

        return "{$storagePath}{$this->getPath()}";
    }

    /**
     * Fetch an instance of static using the checksum.
     *
     * @param  $hash  Checksum
     *
     * @return static|null
     */
    public static function getHash($hash)
    {
        return static::whereHash($hash)->get()->first();
    }

    /**
     * Returns the skip file directoy prefix.
     *
     * @param  $hash  Checksum
     *
     * @return static Like "a/a/a/a"
     */
    public static function getHashPrefix($hash)
    {
        return implode('/', str_split(substr($hash, 0, 4)));
    }

    /**
     * Converts the byte size to a human-readable filesize.
     *
     * @author Jeffrey Sambells
     *
     * @param int $decimals
     *
     * @return string
     */
    public function getHumanFilesize($decimals = 2)
    {
        $bytes = $this->filesize;
        $size = array('B', 'kiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).' '.@$size[$factor];
    }

    /**
     * Returns a URL part based on available information.
     *
     * @return string|int
     */
    public function getIdentifier()
    {
        if (isset($this->pivot)) {
            return $this->pivot->attachment_id;
        }

        return $this->hash;
    }

    /**
     * Returns the relative internal file path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getDirectory().'/'.$this->hash;
    }

    /**
     * Returns the full meta array, if the key is not specified.
     *
     * @param  $key     Defaults to null.
     * @return mixed
     */
    public function getMeta($key = null)
    {
        $meta = json_decode($this->meta, true);

        if (is_null($key)) {
            return $meta;
        }
        elseif (array_key_exists($key, $meta)) {
            return $meta[$key];
        }
        else {
            return false;
        }
    }

    /**
     * Returns a removal URL.
     *
     * @param \App\Board $board
     *
     * @return string
     */
    public function getRemoveUrl(Board $board)
    {
        return $board->getUrl('file.delete', [
            'attachment' => $this->pivot->attachment_id,
        ], false);
    }

    /**
     * Returns a spoiler URL.
     *
     * @param \App\Board $board
     *
     * @return string
     */
    public function getSpoilerUrl(Board $board)
    {
        return $board->getUrl('file.spoiler', [
            'attachment' => $this->pivot->attachment_id,
        ], false);
    }

    /**
     * Returns a string containing class names.
     *
     * @return string
     */
    public function getHtmlClasses()
    {
        $ext = $this->guessExtension();
        $type = 'other';
        $stock = true;
        $spoil = $this->isSpoiler();

        if ($this->isImageVector()) {
            $stock = false;
            $type = 'img';
        }
        elseif ($this->isImage()) {
            $stock = false;
            $type = 'img';
        }
        elseif ($this->isVideo()) {
            $stock = false;
            $type = 'video';
        }
        elseif ($this->isAudio()) {
            $stock = false;
            $type = 'audio';
        }
        else if ($this->isDocument()) {
            $stock = false;
            $type  = "document";
        }

        $classes = [];
        $classes['type'] = "attachment-type-{$type}";
        $classes['ext'] = "attachent-ext-{$ext}";
        $classes['stock'] = $stock ? 'thumbnail-stock' : 'thumbnail-content';
        $classes['spoil'] = $spoil ? 'thumbnail-spoiler' : 'thumbnail-not-spoiler';
        $classHTML = implode(' ', $classes);

        return $classHTML;
    }

    /**
     * Returns an unspoiler URL.
     *
     * @param \App\Board $board
     *
     * @return string
     */
    public function getUnspoilerUrl(Board $board)
    {
        return $board->getUrl('file.unspoiler', [
            'attachment' => $this->pivot->attachment_id,
        ], false);
    }

    /**
     * A dumb way to guess the file type based on the mime.
     *
     * @return string
     */
    public function guessExtension()
    {
        $mimes = explode('/', $this->attributes['mime']);

        switch ($this->attributes['mime']) {
            //#
            // IMAGES
            //#
            case 'image/svg+xml':
                return 'svg';
            case 'image/jpeg':
            case 'image/jpg':
                return 'jpg';
            case 'image/gif':
                return 'gif';
            case 'image/png':
                return 'png';
            //#
            // DOCUMENTS
            //#
            case 'text/plain':
                return 'txt';
            case 'application/epub+zip':
                return 'epub';
            case 'application/pdf':
                return 'pdf';
            //#
            // AUDIO
            //#
            case 'audio/mpeg':
            case 'audio/mp3':
                return 'mp3';
            case 'audio/aac':
                return 'aac';
            case 'audio/mp4':
                return 'mp3';
            case 'audio/ogg':
                return 'ogg';
            case 'audio/wave':
                return 'wav';
            case 'audio/webm':
                return 'wav';
            case 'audio/x-matroska':
                return 'mka';
            //#
            // VIDEO
            //#
            case 'video/3gp':
                return '3gp';
            case 'video/webm':
                return 'webm';
            case 'video/mp4':
                return 'mp4';
            case 'video/ogg':
                return 'ogg';
            case 'video/x-flv':
                return 'flv';
            case 'video/x-matroska':
                return 'mkv';
        }


        if (count($mimes) > 1) {
            return $mimes[1];
        } elseif (count($mimes) === 1) {
            return $mimes[0];
        }

        return 'UNKNOWN';
    }

    /**
     * Returns if the file is present on the disk.
     *
     * @return bool
     */
    public function hasFile()
    {
        return is_readable($this->getFullPath()) && Storage::exists($this->getPath());
    }

    /**
     * Is this attachment audio?
     *
     * @return bool
     */
    public function isAudio()
    {
        switch ($this->attributes['mime']) {
            case 'audio/mpeg':
            case 'audio/mp3':
            case 'audio/aac':
            case 'audio/mp4':
            case 'audio/flac':
            case 'audio/ogg':
            case 'audio/wave':
            case 'audio/webm':
            case 'audio/x-matroska':
                return true;
        }

        return false;
    }

    /**
     * Returns if our pivot is deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        // written in a way to bypass mutators
        return isset($this->attributes['pivot'])
            && isset($this->attributes['pivot']->attributes['is_deleted'])
            && !!$this->attributes['pivot']->attributes['is_deleted'];
    }

    /**
     * Is this attachment a document?
     *
     * @return boolean
     */
    public function isDocument()
    {
        switch ($this->attributes['mime'])
        {
            case "application/epub+zip" :
            case "application/pdf" :
                return true;
        }

        return false;
    }

    /**
     * Is this attachment an image?
     *
     * @return bool
     */
    public function isImage()
    {
        switch ($this->attributes['mime']) {
            case 'image/webp':
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/gif':
            case 'image/png':
                return true;
        }

        return false;
    }

    /**
     * Is this attachment an image vector (SVG)?
     *
     * @reutrn boolean
     */
    public function isImageVector()
    {
        return $this->attributes['mime'] === 'image/svg+xml';
    }

    /**
     * Returns if our pivot is a spoiler.
     *
     * @return bool
     */
    public function isSpoiler()
    {
        // written in a way to bypass mutators
        return isset($this->attributes['pivot'])
            && isset($this->attributes['pivot']->attributes['is_spoiler'])
            && !!$this->attributes['pivot']->attributes['is_spoiler'];
    }

    /**
     * Is this attachment a video?
     * Primarily used to split files on HTTP range requests.
     *
     * @return bool
     */
    public function isVideo()
    {
        switch ($this->attributes['mime']) {
            case 'video/3gp':
            case 'video/webm':
            case 'video/mp4':
            case 'video/ogg':
            case 'video/x-flv':
            case 'video/x-matroska':
                return true;
        }

        return false;
    }

    public function openFile()
    {
        $this->blob = Storage::get($this->getPath());
        return $this->blob;
    }

    /**
     * Work to be done upon creating an attachment using this storage.
     *
     * @param  PostAttachment  $attachment  Defaults to null.
     *
     * @return FileStorage
     */
    public function processAttachment(PostAttachment $attachment = null)
    {
        $this->last_uploaded_at = $this->freshTimestamp();
        // Not counting uploads unless it ends up on a post.
        // $this->upload_count    += 1;

        $this->processThumb();
        $this->save();

        return $this;
    }

    /**
     * Commits the blob to storage.
     */
    public function putFile()
    {
        Storage::makeDirectory($this->getDirectory());
        Storage::put($this->getPath(), $this->blob);
    }

    /**
     * Returns an XML valid attachment HTML string that handles missing thumbnail URLs.
     *
     * @param  null|string|int  $maxWidth Optional. Maximum width constraint. Accepts 'auto'. Defaults null.
     *
     * @return string as HTML
     */
    public function toHtml($dimension = null)
    {
        $ext = $this->guessExtension();
        $url = media_url("static/img/filetypes/{$ext}.svg", false);
        $thumbnail = $this->thumbnails()->first();;

        if ($this->isImageVector()) {
            $url = $this->getUrl();
        }
        else if ($this->isAudio() || $this->isImage() || $this->isVideo() || $this->isDocument()) {
            if ($thumbnail instanceof FileStorage) {
                $url = $thumbnail->getUrl();
            }
            elseif ($this->isAudio()) {
                $url = media_url("static/img/assets/audio.gif", false);
            }
        }

        $classes = $this->getHtmlClasses();
        $mime = $this->attributes['mime'];
        $hash = $this->attributes['hash'];

        if (is_null($dimension)) {
             $dimension = Settings::get('attachmentThumbnailSize', 250);
        }
        else if (is_numeric($dimension)) {
            $dimension = "{$dimension}px";
        }

        return "<div class=\"attachment-wrapper\">" .
            "<img class=\"attachment-img {$classes}\" src=\"{$url}\" data-mime=\"{$mime}\" data-sha256=\"{$hash}\" style=\"max-height: {$dimension}; max-width: {$dimension};\"/>" .
        "</div>";
    }

    /**
     * Refines a query to an exact hash match.
     *
     * @param \Illuminate\Database\Query\Builder $query Supplied by the builder.
     * @param string                             $hash  The checksum hash.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereHash($query, $hash)
    {
        return $query->where('hash', $hash);
    }

    /**
     * Refines a query to only storage items which are orphaned (not used anywhere).
     *
     * @param \Illuminate\Database\Query\Builder $query Supplied by the builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereOrphan($query)
    {
        return $query->whereDoesntHave('postAttachments')
            ->whereDoesntHave('assets');
    }
}
