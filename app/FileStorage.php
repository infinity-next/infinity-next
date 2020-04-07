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
        'source_id' => "int",
        'hash' => 'string',
        'phash' => 'int',
        'filesize' => "int",
        'file_width' => "int",
        'file_height' => "int",
        'mime' => "string",
        'meta' => "string",
        'banned_at' => "datetime",
        'first_uploaded_at' => "datetime",
        'last_uploaded_at' => "datetime",
        'upload_count' => "int",
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'phash',
        'banned_at',
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
     * Ties database triggers to the model.
     *
     * @static
     */
    public static function boot()
    {
        parent::boot();

        // When being created, commit blob data.
        static::creating(function ($storage) {
            Storage::makeDirectory($storage->getDirectory());
            Storage::put($storage->getPath(), $storage->blob);

            $storage->filesize = Storage::size($storage->getPath());
            $storage->first_uploaded_at = now();
            $storage->last_uploaded_at = now();
            $storage->upload_count = $storage->upload_count ?? 1;

            return Storage::exists($storage->getPath());
        });
    }

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

    public function thumbnail()
    {
        return $this->hasOne(static::class, 'source_id');
    }

    public function thumbnails()
    {
        return $this->hasMany(static::class, 'source_id');
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
        $query = Post::where('board_uri', $board->board_uri);

        if (!is_null($thread)) {
            $query = $query->whereInThread($thread);
        }

        return $query->whereHas('postAttachments', function ($query) use ($hash) {
            $query->whereHash($hash);
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
     * Creates a new PostAttachment for a post using a hash.
     *
     * @param Post   $post
     * @param string $filename
     * @param bool   $spoiler
     *
     * @return PostAttachment
     */
    public function createAttachmentWithThis(Post $post, $filename, $spoiler = false, $autosave = true)
    {
        ## TODO ## Move this somewhere that makes more sense..
        $fileName = pathinfo($filename, PATHINFO_FILENAME);
        $fileExt = $this->guessExtension();

        $attachment = new PostAttachment;
        $attachment->post_id = $post->post_id;
        $attachment->file_id = $this->file_id;
        $attachment->filename = urlencode("{$fileName}.{$fileExt}");
        $attachment->is_spoiler = (bool) $spoiler;

        if ($autosave) {
            $attachment->save();

            ++$this->upload_count;
            $this->save();
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
     * Returns the attachment's base filename.
     *
     * @return string
     */
    public function getBaseFileName()
    {
        $pathinfo = pathinfo($this->pivot->filename);

        return $pathinfo['filename'];
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
     * Supplies a download name.
     *
     * @return string
     */
    public function getDownloadName()
    {
        return "{$this->getFileName()}.{$this->guessExtension()}";
    }

    /**
     * Supplies a clean URL for downloading an attachment on a board.
     *
     * @param App\Board $board
     *
     * @return string
     */
    public function getUrl(Board $board)
    {
        $params = [
            'hash' => $this->hash,
            'filename' => $this->getDownloadName(),
        ];

        if (!config('app.url_media', false)) {
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
        if ($this->hasThumb()) {
            return "{$this->file_width}x{$this->file_height}";
        }

        return;
    }

    /**
     * Determines and returns the "xxx" of "/url/xxx.ext" for URLs.
     *
     * @param string|null $format Optional. The token syntax for the filename. Defaults to site setting.
     *
     * @return string
     */
    public function getFileName($nameFormat = null)
    {
        if (is_null($nameFormat)) {
            // Build a thumbnail using the admin settings.
            $nameFormat = Settings::get('attachmentName');
        }

        $bits['t'] = $this->first_uploaded_at->timestamp;
        $bits['i'] = 0;
        $bits['n'] = $bits['t'];

        if (isset($this->pivot)) {
            if (isset($this->pivot->position)) {
                $bits['i'] = $this->pivot->position;
            }

            if (isset($this->pivot->filename)) {
                $bits['n'] = $this->getBaseFileName();
            }
        }

        $attachmentName = $nameFormat;

        foreach ($bits as $bitKey => $bitVal) {
            $attachmentName = str_replace("%{$bitKey}", $bitVal, $attachmentName);
        }

        return $attachmentName;
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
        return implode(str_split(substr($hash, 0, 4)), '/');
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
     * Truncates the middle of a filename to show extension.
     *
     * @return string Filename.
     */
    public function getShortFilename()
    {
        if (isset($this->pivot) && isset($this->pivot->filename)) {
            $filename = urldecode($this->pivot->filename);

            if (mb_strlen($filename) <= 20) {
                return $filename;
            }

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $name = mb_substr($name, 0, 15);

            return "{$name}... .{$ext}";
        }

        return $this->getFileName();
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
    public function getThumbnailClasses()
    {
        $ext = $this->guessExtension();
        $type = 'other';
        $stock = true;
        $spoil = $this->isSpoiler();

        if ($this->isImageVector()) {
            $stock = false;
            $type = 'img';
        } elseif ($this->isImage()) {
            if ($this->hasThumb()) {
                $stock = false;
                $type = 'img';
            }
        } elseif ($this->isVideo()) {
            if ($this->hasThumb()) {
                $stock = false;
                $type = 'video';
            }
        } elseif ($this->isAudio()) {
            $stock = false;
            $type = 'audio';
        }
        else if ($this->isDocument())
        {
            if ($this->hasThumb())
            {
                $stock = false;
                $type  = "document";
            }
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
     * Returns an XML valid attachment HTML string that handles missing thumbnail URLs.
     *
     * @param \App\Board $board    The board this thumbnail will belong to.
     * @param int        $maxWidth Optional. Maximum width constraint. Defaults null.
     *
     * @return string as HTML
     */
    public function getThumbnailHtml(?Board $board = null, $maxDimension = null)
    {
        $ext = $this->guessExtension();
        $mime = $this->mime;
        $url = media_url("static/img/filetypes/{$ext}.svg", false);
        $spoil = $this->isSpoiler();
        $deleted = $this->isDeleted();
        $hash = $deleted ? null : $this->hash;

        if ($deleted) {
            $url = $board->getAssetUrl('file_deleted');
        }
        elseif ($spoil) {
            $url = $board->getAssetUrl('file_spoiler');
        }
        elseif ($this->isImageVector()) {
            $url = $this->getDownloadUrl($board);
        }
        elseif ($this->isAudio() || $this->isImage() || $this->isVideo() || $this->isDocument()) {
            if ($this->thumbnail instanceof FileStorage) {
                $url = $this->thumbnail->getUrl($board);
            }
            elseif ($this->isAudio()) {
                $url = media_url("static/img/assets/audio.gif", false);
            }
        }

        $classHTML = $this->getThumbnailClasses();

        // Measure dimensions.
        $height = 'auto';
        $width = 'auto';
        $maxWidth = 'none';
        $maxHeight = 'none';
        $minWidth = 'none';
        $minHeight = 'none';
        $oHeight = $this->thumbnail ? $this->thumbnail->file_height : Settings::get('attachmentThumbnailSize', 250);
        $oWidth = $this->thumbnail ? $this->thumbnail->file_width : Settings::get('attachmentThumbnailSize', 250);

        if ($this->has_thumbnail && !$this->isSpoiler() && !$this->isDeleted()) {
            $height = $oHeight.'px';
            $width = $this->thumbnail_width.'px';

            if (is_int($maxDimension) && ($oWidth > $maxDimension || $oHeight > $maxDimension)) {
                if ($oWidth > $oHeight) {
                    $height = 'auto';
                    $width = $maxDimension.'px';
                }
                elseif ($oWidth < $oHeight) {
                    $height = $maxDimension.'px';
                    $width = 'auto';
                }
                else {
                    $height = $maxDimension.'px';
                    $width = $maxDimension.'px';
                }
            }

            $minWidth = $width;
            $minHeight = $height;
        }
        else {
            $maxWidth = Settings::get('attachmentThumbnailSize', 250).'px';
            $maxHeight = $maxWidth;
            $width = $maxWidth;
            $height = 'auto';

            if (is_int($maxDimension)) {
                $maxWidth = "{$maxDimension}px";
                $maxHeight = "{$maxDimension}px";
            }

            if ($this->isSpoiler() || $this->isDeleted()) {
                $minHeight = 'none';
                $minWidth = 'none';
                $width = $maxWidth;
            }
        }

        return "<div class=\"attachment-wrapper\" style=\"height: {$height}; width: {$width};\">" .
            "<img class=\"attachment-img {$classHTML}\" src=\"{$url}\" data-mime=\"{$mime}\" data-sha256=\"{$hash}\" style=\"height: {$height}; width: {$width};\"/>" .
        "</div>";
    }

    /**
     * Supplies a clean thumbnail URL for embedding an attachment on a board.
     *
     * @param \App\Board $board
     *
     * @return string
     */
    public function getThumbnailUrl(?Board $board = null)
    {
        $ext = $this->guessExtension();

        if ($board instanceof Board && $this->isSpoiler()) {
            return $board->getSpoilerUrl();
        }

        if ($this->isImage() || $this->isDocument() || $this->isVideo()) {
            $ext = "webp";
        }
        elseif ($this->isAudio()) {
            if (!$this->hasThumb()) {
                if ($board instanceof Board) {
                    return $board->getAudioArtURL();
                }
                else {
                    return asset('static/img/assets/audio.gif');
                }
            }

            $ext = 'webp';
        }
        elseif ($this->isImageVector()) {
            // With the SVG filetype, we do not generate a thumbnail, so just serve the actual SVG.
            return $this->getDownloadUrl($board);
        }

        if ($board instanceof Board) {
            $params = [
                'board' => $board,
                'hash' => $this->thumbnail->hash,
                'filename' => "thumb_".$this->getDownloadName().".{$ext}",
            ];

            return route('static.file.hash', $params, false);
        }

        return route('panel.site.files.send', [
            'hash' => $this->hash,
            'filename' => "{$this->file_id}.{$ext}",
        ], false);
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
        $mimes = explode('/', $this->mime);

        switch ($this->mime) {
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
     * Returns if a thumbnail is present on the disk.
     *
     * @return bool
     */
    public function hasThumb()
    {
        return $this->thumbnails->count() > 0;
    }

    /**
     * Is this attachment audio?
     *
     * @return bool
     */
    public function isAudio()
    {
        switch ($this->mime) {
            case 'audio/mpeg':
            case 'audio/mp3':
            case 'audio/aac':
            case 'audio/mp4':
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
        return isset($this->pivot)
            && isset($this->pivot->is_deleted)
            && (bool) $this->pivot->is_deleted;
    }

    /**
     * Is this attachment a document?
     *
     * @return boolean
     */
    public function isDocument()
    {
        switch ($this->mime)
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
        switch ($this->mime) {
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
        return $this->mime === 'image/svg+xml';
    }

    /**
     * Returns if our pivot is a spoiler.
     *
     * @return bool
     */
    public function isSpoiler()
    {
        return isset($this->pivot) && isset($this->pivot->is_spoiler) && (bool) $this->pivot->is_spoiler;
    }

    /**
     * Is this attachment a video?
     * Primarily used to split files on HTTP range requests.
     *
     * @return bool
     */
    public function isVideo()
    {
        switch ($this->mime) {
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
