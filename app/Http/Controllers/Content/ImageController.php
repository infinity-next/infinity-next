<?php namespace App\Http\Controllers\Content;

use App\FileStorage;
use App\FileAttachment;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

use File;
use Storage;
use Response;

class ImageController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Image Controller
	|--------------------------------------------------------------------------
	|
	| Handles requests for static image content.
	| 
	*/
	
	/**
	 * Delivers an image.
	 *
	 * @param  string  $hash
	 * @param  string  $filename
	 * @param  boolean  $thumbnail
	 * @return Response
	 */
	public function getImage($hash = false, $filename = false, $thumbnail = false)
	{
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			header('HTTP/1.1 304 Not Modified');
			die();
		}
		
		if ($hash !== false && $filename !== false)
		{
			$FileStorage     = FileStorage::getHash($hash);
			$storagePath     = !$thumbnail ? $FileStorage->getPath()     : $FileStorage->getPathThumb();
			$storagePathFull = !$thumbnail ? $FileStorage->getFullPath() : $FileStorage->getFullPathThumb();
			$cacheTime       =  31536000; /// 1 year
			
			if ($FileStorage instanceof FileStorage && Storage::exists($storagePath))
			{
				ini_set("zlib.output_compression", "Off");
				
				$responseSize    = Storage::size($storagePath);
				$responseCode    = 200;
				$responseHeaders = [
					'Cache-Control'        => "public, max-age={$cacheTime}, pre-check={$cacheTime}",
					'Expires'              => gmdate(DATE_RFC1123, time() + $cacheTime),
					'Last-Modified'        => gmdate(DATE_RFC1123, File::lastModified($storagePathFull)),
					'Content-Disposition'  => "inline",
					//'Content-Disposition'  => "attachment; filename={$filename}",
					'Content-Length'       => $responseSize,
					'Content-Type'         => $FileStorage->mime,
					'Filename'             => $filename,
				];
				
				
				if ($thumbnail)
				{
					if ($FileStorage->isImage())
					{
						$responseHeaders['Content-Type'] = Settings::get('attachmentThumbnailJpeg') ? "image/jpg" : "image/png";
					}
					else if ($FileStorage->isVideo())
					{
						$responseHeaders['Content-Type'] = "image/jpg";
					}
					else if ($FileStorage->isAudio())
					{
						$responseHeaders['Content-Type'] = "image/png";
					}
				}
				
				
				// Determine if we can skip PHP content distribution.
				// This is hugely important.
				//
				// APACHE
				// Relies on the mod_xsendfile module.
				if (function_exists("apache_get_modules") && in_array("mod_xsendfile", apache_get_modules()))
				{
					$xSendFile = true;
					$responseHeaders['X-Sendfile'] = $storagePathFull;
				}
				// NGINX
				else if (preg_match("/nginx\/1(\.[0-9]+)+/", $_SERVER['SERVER_SOFTWARE']))
				{
					$xSendFile = true;
					$responseHeaders['X-Accel-Redirect'] = "/{$storagePath}";
				}
				// LIGHTTPD
				else if (preg_match("/lighttpd\/1(\.[0-9]+)+/", $_SERVER['SERVER_SOFTWARE']))
				{
					$xSendFile = true;
					$responseHeaders['X-LIGHTTPD-send-file'] = $storagePathFull;
				}
				
				
				// Seek Audio and Video files.
				$responseStart = 0;
				$responseEnd   = $responseSize - 1;
				
				if ($FileStorage->isVideo())
				{
					$responseHeaders['Accept-Ranges'] = "0-" . ($responseSize - 1);
					
					if (isset($_SERVER['HTTP_RANGE']))
					{
						list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
						
						if (strpos($range, ',') !== false)
						{
							return Response::make("Requested Range Not Satisfiable", 416, [
								'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
							]);
						}
						
						if ($range == '-')
						{
							$responseStart = $this->size - substr($range, 1);
						}
						else
						{
							$range = explode('-', $range);
							$responseStart = $range[0];
							
							$responseEnd = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $responseEnd;
						}
						
						$responseEnd = ($responseEnd > $responseEnd) ? $responseEnd : $responseEnd;
						
						if ($responseStart > $responseEnd || $responseStart > $responseSize - 1 || $responseEnd >= $responseSize)
						{
							return Response::make("Requested Range Not Satisfiable", 416, [
								'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
							]);
						}
						
						$responseCode = 206;
						$responseHeaders['Content-Length'] = $responseSize - $responseStart;
						$responseHeaders['Content-Range']  = "bytes {$responseStart}-{$responseEnd}/{$responseSize}";
						
						unset($responseHeaders['Accept-Ranges']);
						unset($responseHeaders['Cache-Control']);
						unset($responseHeaders['Content-Disposition']);
						unset($responseHeaders['Expires']);
					}
				}
				
				
				// Are we using the webserver to send files?
				if ($xSendFile)
				{
					// Yes.
					// Send an empty 200 response with the headers.
					return Response::make("", $responseCode, $responseHeaders);
				}
				else
				{
					// No.
					// Get our hands dirty and stream the file.
					return Response::stream(function() use ($storagePathFull, $responseStart, $responseEnd) {
						if (!($responseStream = fopen($storagePathFull, 'rb'))) {
							abort(500, "Could not open requested file.");
						}
						
						if ($responseStart > 0)
						{
							fseek($responseStream, $responseStart);
						}
						
						$streamCurrent = 0;
						
						while (!feof($responseStream) && $streamCurrent < $responseEnd && connection_status() == 0)
						{
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
	
	/**
	 * Delivers a file's thumbnail by rerouting the request to getFile with an optional parameter set.
	 *
	 * @param  $string  $hash
	 * @param  $string  $filename
	 * @return Response
	 */
	public function getThumbnail($hash = false, $filename = false)
	{
		return $this->getImage($hash, $filename, true);
	}
}
