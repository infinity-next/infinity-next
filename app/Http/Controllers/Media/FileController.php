<?php

namespace App\Http\Controllers\Media;

use App\Board;
use App\FileStorage;
use App\FileAttachment;
use App\Events\AttachmentWasModified;
use App\Http\Controllers\Controller;
use App\Http\SendsFilesTrait as SendsFiles;
use File;
use Settings;
use Storage;
use Request;
use Response;
use Validator;
use Event;

/**
 * Delivers static content from across the site.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class FileController extends Controller
{
    use SendsFiles;

    /**
     * Delivers a file from an attachment.
     *
     * @param \App\FileAttachment $attachment
     * @param string              $filename
     */
    public function getImageFromAttachment(FileAttachment $attachment, $filename = false)
    {
        if (!$attachment->exists || !$attachment->storage) {
            return abort(404);
        }

        return $this->sendFile($attachment->storage->hash, $filename);
    }

    /**
     * Delivers a file from a hash.
     *
     * @param string $hash
     * @param string $filename
     */
    public function getImageFromHash($hash = false, $filename = false)
    {
        return $this->sendFile($hash, $filename, false);
    }

    /**
     * Delivers a file's thumbnail by rerouting the request to getFile with an optional parameter set.
     *
     * @param \App\FileAttachment $attachment
     * @param  $string  $filename
     *
     * @return Response
     */
    public function getThumbnailFromAttachment(FileAttachment $attachment, $filename = false)
    {
        if ($attachment->storage) {
            return $this->sendFile($attachment->storage->hash, $filename, true);
        }

        return abort(404);
    }

    /**
     * Delivers a file from a hash.
     *
     * @param string $hash
     * @param string $filename
     */
    public function getThumbnailFromHash($hash = false, $filename = false)
    {
        return $this->sendFile($hash, $filename, true);
    }
}
