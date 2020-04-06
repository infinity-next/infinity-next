<?php

namespace App\Http\Controllers;

use App\FileStorage;
use File;
use Request;
use Response;
use Storage;

/**
 * A trait allowing controllers to deliver static content uniformly.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
trait SendsFilesTrait
{
    /**
     * Delivers a file from storage by the most expedient means available.
     *
     * @param string|\App\FileStorage  $attachment  The FileStorage model or its hash.
     * @param string                   $filename    The name attached to the file.
     * @param bool                     $thumbnail   If we're sending the thumbnailed version or the full file.
     *
     * @return Response
     */
    public function sendFile($hash = false, $filename = false)
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        $FileStorage = null;

        if ($hash instanceof FileStorage) {
            $FileStorage = $hash;
            $hash = $FileStorage->hash;
        }
        if (is_string($hash)) {
            $FileStorage = FileStorage::getHash($hash);

            if (!is_string($filename)) {
                $filename = "{$hash}.{$FileStorage->guessExtension()}";
            }
        }

        if ($FileStorage instanceof FileStorage) {
            $storagePath = $FileStorage->getPath();
            $storagePathFull = $FileStorage->getFullPath();
            $cacheTime = 31536000; /// 1 year

            if (is_link($storagePathFull)) {
                if (!is_readable($storagePathFull)) {
                    abort(500, 'Symlink file is unreadable.');
                }

                $storageExists = file_exists($storagePathFull);
            }
            else {
                $storageExists = Storage::exists($storagePath);
            }


            if ($storageExists) {
                ini_set('zlib.output_compression', 'Off');

                $responseSize = filesize($storagePathFull);
                $responseCode = 200;
                $responseHeaders = [
                    'Cache-Control' => "public, max-age={$cacheTime}, pre-check={$cacheTime}",
                    'Expires' => gmdate(DATE_RFC1123, time() + $cacheTime),
                    'Last-Modified' => gmdate(DATE_RFC1123, File::lastModified($storagePathFull)),
                    'Content-Disposition' => Request::input('disposition', 'inline'),
                    //'Content-Disposition'  => "attachment; filename={$filename}",
                    'Content-Length' => $responseSize,
                    'Content-Type' => $FileStorage->mime,
                    'Filename' => urldecode($filename),
                ];

                // Determine if we can skip PHP content distribution.
                // This is hugely important.
                $xSendFile = false;

                // APACHE
                // Relies on the mod_xsendfile module.
                if (function_exists('apache_get_modules') && in_array('mod_xsendfile', apache_get_modules())) {
                    $xSendFile = true;
                    $responseHeaders['X-Sendfile'] = $storagePathFull;
                }
                // NGINX
                elseif (preg_match("/nginx\/1(\.[0-9]+)+/", $_SERVER['SERVER_SOFTWARE'])) {
                    $xSendFile = true;
                    $responseHeaders['X-Accel-Redirect'] = "/{$storagePath}";
                }
                // LIGHTTPD
                elseif (preg_match("/lighttpd\/1(\.[0-9]+)+/", $_SERVER['SERVER_SOFTWARE'])) {
                    $xSendFile = true;
                    $responseHeaders['X-LIGHTTPD-send-file'] = $storagePathFull;
                }


                // Seek Audio and Video files.
                $responseStart = 0;
                $responseEnd = $responseSize - 1;

                if ($FileStorage->isVideo()) {
                    $responseHeaders['Accept-Ranges'] = '0-'.($responseSize - 1);

                    if (isset($_SERVER['HTTP_RANGE'])) {
                        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                        if (strpos($range, ',') !== false) {
                            return Response::make('Requested Range Not Satisfiable', 416, [
                                'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
                            ]);
                        }

                        if ($range == '-') {
                            $responseStart = $this->size - substr($range, 1);
                        } else {
                            $range = explode('-', $range);
                            $responseStart = $range[0];

                            $responseEnd = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $responseEnd;
                        }

                        if ($responseStart > $responseEnd || $responseStart > $responseSize - 1 || $responseEnd >= $responseSize) {
                            return Response::make('Requested Range Not Satisfiable', 416, [
                                'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
                            ]);
                        }

                        $responseCode = 206;
                        $responseHeaders['Content-Length'] = $responseSize - $responseStart;
                        $responseHeaders['Content-Range'] = "bytes {$responseStart}-{$responseEnd}/{$responseSize}";

                        unset($responseHeaders['Accept-Ranges']);
                        unset($responseHeaders['Cache-Control']);
                        unset($responseHeaders['Content-Disposition']);
                        unset($responseHeaders['Expires']);
                    }
                }


                // Are we using the webserver to send files?
                if ($xSendFile) {
                    // Yes.
                    // Send an empty 200 response with the headers.
                    return Response::make('', $responseCode, $responseHeaders);
                }
                else {
                    // No.
                    // Get our hands dirty and stream the file.
                    return Response::make(function () use ($storagePathFull, $responseStart, $responseEnd) {
                        if (!($responseStream = fopen($storagePathFull, 'rb'))) {
                            abort(500, 'Could not open requested file.');
                        }

                        if ($responseStart > 0) {
                            fseek($responseStream, $responseStart);
                        }

                        $streamCurrent = 0;

                        while (!feof($responseStream) && $streamCurrent < $responseEnd && connection_status() == 0) {
                            echo fread($responseStream, min(1024 * 16, $responseEnd - $responseStart + 1));
                            $streamCurrent += 1024 * 16;
                        }

                        fclose($responseStream);
                    }, $responseCode, $responseHeaders);
                }
            }
        }

        return abort(404);
    }
}
