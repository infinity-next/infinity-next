<?php

namespace App\Filesystem;

use App\FileStorage;
use App\Support\IP;
use InfinityNext\Sleuth\FileSleuth;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Jenssegers\ImageHash\Implementations\PerceptualHash;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cache;
use File;
use Storage;
use Settings;

/**
 * Fuzzy checks file against hashes.
 *
 * @category   Filesystem
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class Upload
{
    /**
     * If true, this file was uploaded by a user.
     *
     * @var  bool
     */
    protected $isClientFile = false;

    /**
     * The initial data sent to upload.
     *
     * @var  SymfonyFile|UploadedFile  $file
     */
    protected $file;

    /**
     * The storage model.
     *
     * @var  App\FileStorage  $storage
     */
    protected $storage;

     /**
      * A collection of storage models for processed thumbnails.
      *
      * @var  Collection  $thumbnails
      */
    protected $thumbnails;

    public function __construct($file = null)
    {
        $this->thumbnails = collect([]);

        if (!is_null($file)) {
            $this->open($file);
        }
    }

    public function open($file)
    {
        if ($file instanceof FileStorage) {
            return $this->openStorage($file);
        }

        if ($file instanceof SymfonyFile || $file instanceof UploadedFile) {
            if ($file instanceof UploadedFile) {
                $this->isClientFile = true;
            }

            return $this->openFile($file);
        }

        throw new \InvalidArgumentException('Attempted to open a file with an unexpected type.');
    }

    /**
     * Handles an UploadedFile from form input. Stores, creates a model, and generates a thumbnail.
     *
     * @param  SymfonyFile|UploadedFile  $file
     *
     * @return App\FileStorage
     */
     protected function openFile($file)
     {
         $this->file = $file;
         $data = (string) File::get($file);

         // SHA-256 hash and check for re-up.
         $hash = hash('sha256', $data);

         // Deduplicate
         $storage = FileStorage::firstOrNew([
             'hash' => $hash,
         ]);

         $storage->blob = $data;
         unset($data);

         $this->storage = $storage;
         if ($storage->exists) {
             return $this->openStorage($storage);
         }

         return $storage;
     }

     /**
      * Sets up the class instance with an existing file model.
      *
      * @param  App\FileStorage  $file
      *
      * @return App\FileStorage
      */
     protected function openStorage($storage)
     {
         if(!is_null($storage->banned_at)) {
             throw new \Exception('File is explicitly banned.');
         }

         $storage->openFile();

         $this->storage = $storage;
         return $storage;
     }

    public function cacheBandwidth(?IP $ip = null)
    {
        $ip = $ip ?? new IP;
        $uploadSize = (int) Cache::get('upstream_data_for_'.$ip->toLong(), 0);

        if ($uploadSize <= 52430000) {
            Cache::increment('upstream_data_for_'.$ip->toLong(), $file->getSize(), 2);

            $newStorage = FileStorage::storeUpload($file);
            $storage[$newStorage->hash] = $newStorage;

            Cache::decrement('upstream_data_for_'.$ip->toLong(), $file->getSize());
        }
        else {
            return abort(429);
        }
    }

    /**
     * Perceptually hashes supplied content and checks it against the database.
     *
     * @return int  An unsigned bigint safe for pgsql databases.
     */
    public function phash($content)
    {
        // phash
        $hasher = new ImageHash(new PerceptualHash());

        $fileHash = $hasher->hash($content);
        $filePhash = gmp_sub(gmp_init("0x{$fileHash->toHex()}", 16), gmp_pow(2, 63));

        FileStorage::whereNotNull('banned_at')->whereNotNull('phash')->pluck('phash')->each(function ($theirPhash) use ($filePhash) {
            $theirPhash = gmp_add(gmp_init($theirPhash, 10), gmp_pow(2, 63));

            $distance = gmp_hamdist($filePhash, $theirPhash);
            if ($distance < 16) {
                app('log')->error("Banned image: ".(new IP)->toText()." uploaded a file with a perceptual similarity to banned content (with a hamming distance of {$distance}).");
                throw new \Exception("This file has a perceptual similarity to banned content (with a hamming distance of {$distance}).");
                return false;
            }
        });

        return gmp_strval($filePhash, 10);
    }

    /**
     * Uploads the file into the file system, post-proceses, and returns storage model.
     *
     * @param  bool  $thumbnail  If thumbanil(s) should be created. Defaults true.
     *
     * @return \App\FileStorage
     */
    public function process($thumbnail = true)
    {
        $file = $this->file;
        $storage = $this->storage;

        if ($storage->exists) {
            $storage->last_uploaded_at = now();
            $storage->upload_count += 1;
        }
        else {
            $storage->hash = hash('sha256', $storage->blob);
            $storage->filesize = $file->getSize();
            $storage->mime = $this->isClientFile ? $file->getClientMimeType() : $file->getMimeType();
            $storage->first_uploaded_at = now();

            if (!isset($file->case)) {
                $ext = $file->guessExtension();

                $sleuth = new FileSleuth($file);
                $file->case = $sleuth->check($file->getRealPath(), $ext);

                if (!$file->case) {
                    $file->case = $sleuth->check($file->getRealPath());
                }
            }

            if (is_object($file->case)) {
                $storage->mime = $file->case->getMimeType();

                if ($file->case->getMetaData()) {
                    $storage->meta = json_encode($file->case->getMetaData());
                }
            }

            if ($storage->isImage()) {
                $storage->phash = $this->phash($storage->blob);
                $image = (new ImageManager())->make($storage->blob);
                $storage->file_height = $image->height();
                $storage->file_width = $image->width();
            }
        }

        if ($thumbnail) {
            $this->processThumbnails();
            $storage->save();
            $storage->thumbnails()->saveMany($this->thumbnails);
        }
        else {
            $storage->save();
        }

        return $storage;
    }

    /**
     * Accepts image data and returns a FileStorage object.
     *
     * @return FileStorage
     */
    public function processThumbnail($content)
    {
        $image = (new ImageManager())->make($content)
            ->resize(Settings::get('attachmentThumbnailSize', 200), Settings::get('attachmentThumbnailSize', 200), function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', Settings::get('attachmentThumbnailQuality', 75));

        $blob = (string) $image;
        $hash = hash('sha256', $blob);
        $storage = FileStorage::firstOrNew([
            'hash' => $hash,
        ]);

        $storage->blob = $blob;
        unset($blob);

        if (!$storage->exists) {
            $storage->file_height = $image->height();
            $storage->file_width = $image->width();
            $storage->hash = $hash;
            $storage->mime = "image/webp";
            $storage->upload_count = 1;
            $storage->phash = $this->phash($storage->blob);
        }
        else {
            $storage->last_uploaded_at = now();
            $storage->upload_count += 1;
        }

        $storage->save();
        $this->thumbnails->push($storage);
        return $storage;
    }

    public function processThumbnails()
    {
        // skip thumbnailing if we already have thumbnails
        if ($this->storage->exists) {
            $this->storage->load('thumbnails');

            if ($this->storage->thumbnails->count() > 0) {
                $this->thumbnails = $this->storage->thumbnails;
                return $this->thumbnails;
            }
        }

        // thumbnail based on upload type
        if ($this->storage->isAudio()) {
            $this->processThumbnailsForAudio();
        }
        elseif ($this->storage->isVideo()) {
            $this->processThumbnailsForVideo();
        }
        elseif ($this->storage->mime === "application/epub+zip") {
            $this->processThumbnailsForBook();
        }
        elseif ($this->storage->isImage()) {
            $this->processThumbnailsForImage();
        }

        $this->storage->setRelation('thumbnails', $this->thumbnails);
        return $this->thumbnails;
    }

    /**
     * Extracts album art from an audio source's ID3 meta data.
     *
     * @return void
     */
    protected function processThumbnailsForAudio()
    {
        $temp = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($temp, $this->storage->blob);

        $id3 = new \getID3();
        $meta = $id3->analyze($temp);

        if (isset($meta['comments']['picture']) && !empty($meta['comments']['picture'])) {
            foreach ($meta['comments']['picture'] as $albumArt) {
                //try {
                    $this->processThumbnail($albumArt['data']);
                //}
                //catch (\Exception $error) {
                //app('log')->error("Encountered an error trying to generate a thumbnail for audio {$this->hash}.");
                //}
            }
        }

        if (isset($meta['id3v2']['APIC']) && !empty($meta['id3v2']['APIC'])) {
            foreach ($meta['id3v2']['APIC'] as $apic) {
                if (!isset($apic['data'])) {
                    continue;
                }

                $this->processThumbnail($apic['data']);
            }
        }
    }

    protected function processThumbnailsForBook()
    {
        $temp = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($temp, $this->storage->blob);

        $epub = new \ZipArchive();
        $epub->open($temp);

        // Find and parse the rootfile
        $container     = $epub->getFromName("META-INF/container.xml");
        $containerXML  = simplexml_load_string($container);
        $rootFilePath  = $containerXML->rootfiles->rootfile[0]['full-path'];
        $rootFile      = $epub->getFromName($rootFilePath);
        $rootFileXML   = simplexml_load_string($rootFile);

        // Determine base directory
        $rootFileParts = pathinfo($rootFilePath);
        $baseDirectory = ($rootFileParts['dirname'] == "." ? null : $rootFileParts['dirname']);

        // XPath queries with namespaces are shit until XPath 2.0 so we hold its hand
        $rootFileNS    = $rootFileXML->getDocNamespaces();

        $ns = "";
        if (isset($rootFileNS[""])) {
            $rootFileXML->registerXPathNamespace("default", $rootFileNS[""]);
            $ns = "default:";
        }

        // Non-standards used with OEB, prior to EPUB
        $oebXPath   = "//{$ns}reference[@type='coverimagestandard' or @type='other.ms-coverimage-standard']";
        // EPUB standards
        $epubXPath = "//{$ns}item[@properties='cover-image' or @id=(//{$ns}meta[@name='cover']/@content)]";

        // Query the rootfile for cover elements
        $coverXPath = $rootFileXML->xpath("{$oebXPath} | {$epubXPath}");

        if ($coverXPath) {
            // Get real cover entry name and read it
            $coverHref   = $coverXPath[0]['href'];
            $coverEntry  = (is_null($baseDirectory) ? $coverHref : $baseDirectory . "/" . $coverHref);
            $coverString = $epub->getFromName($coverEntry);

            try {
                $cover = imagecreatefromstring($coverString);
                $this->processThumbnail($cover);
            }
            catch (\Exception $e)     {
                app('log')->error("Encountered an error trying to generate a thumbnail for book {$this->hash}.");
            }
        }
    }

    /**
     * Thumbnails an image upload.
     *
     * @return void
     */
    protected function processThumbnailsForImage()
    {
        $this->processThumbnail($this->storage->blob);
    }

    protected function processThumbnailsForVideo()
    {
        $output = "Haven't executed once yet."; // debug string
        $thumbPath = stream_get_meta_data(tmpfile())['uri'];
        $videoPath = stream_get_meta_data(tmpfile())['uri'];
        file_put_contents($videoPath, $this->storage->blob);

        // get duration
        $time = exec(env('LIB_FFMPEG', 'ffmpeg')." -i {$videoPath} 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//", $output, $returnvalue);

        // duration in seconds; half the duration = middle
        $durationBits = explode(':', $time);
        $durationSeconds = (float) $durationBits[2] + ((int) $durationBits[1] * 60) + ((int) $durationBits[0] * 3600);
        $durationMiddle = $durationSeconds / 2;


        $tsHours = str_pad(floor($durationMiddle / 3600), 2, '0', STR_PAD_LEFT);
        $tsMinutes = str_pad(floor($durationMiddle / 60 % 3600), 2, '0', STR_PAD_LEFT);
        $tsSeconds = str_pad(number_format($durationMiddle % 60, 2), 5, '0', STR_PAD_LEFT);
        $timestamp = "{$tsHours}:{$tsMinutes}:{$tsSeconds}";

        $cmd = env('LIB_FFMPEG', 'ffmpeg').' '.
                "-i {$videoPath} ".// Input video.
                //"-filter:v yadif " . // Deinterlace.
                '-deinterlace '.
                '-an '.// No audio.
                "-ss {$timestamp} ".// Timestamp for our thumbnail.
                '-f mjpeg '.// Output format.
                '-t 1 '.// Duration in seconds.
                '-r 1 '.// FPS, 1 for 1 frame.
                '-y '.// Overwrite file if it already exists.
                '-threads 1 '.
                "{$thumbPath} 2>&1";

        exec($cmd, $output, $returnvalue);
        app('log')->info($output);

        // Constrain thumbnail to proper dimensions.
        if (filesize($thumbPath)) {
            $this->processThumbnail(file_get_contents($thumbPath));
        }
        else {
            app('log')->error("Video thumbnail has no size for file {$this->storage->hash}.");
        }
    }
}
