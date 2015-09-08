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
			$cacheTime       =  315360000; /// 10 years
			
			if ($FileStorage instanceof FileStorage && Storage::exists($storagePath))
			{
				$responseSize    = Storage::size($storagePath);
				$responseCode    = 400;
				$responseHeaders = [
					'Cache-Control'       => "public, max-age={$cacheTime}, pre-check={$cacheTime}",
					'Expires'             => gmdate(DATE_RFC1123, time() + $cacheTime),
					'Last-Modified'       => gmdate(DATE_RFC1123, File::lastModified($storagePathFull)),
					'Content-Disposition' => "inline",
					'Content-Length'      => $responseSize,
					//'Content-Type'        => $FileStorage->mime,
					'Filename'            => $filename,
				];
				
				$responseStart = 0;
				$responseEnd   = $responseSize - 1;
				
				//if (!($responseStream = fopen($storagePathFull, 'rb'))) {
				//	abort(500, "Could not open requested file.");
				//}
				
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
						
						if ($responseStart > $responseEnd || $responseStart > $responseSize - 1 || $responseEnd >= $this->size)
						{
							return Response::make("Requested Range Not Satisfiable", 416, [
								'Content-Range' => "bytes {$responseStart}-{$responseEnd}/{$responseSize}",
							]);
						}
						
						$length = $responseEnd - $responseStart + 1;
						
						//fseek($responseStream, $responseStart);
						
						$responseHeaders['Content-Range'] = "bytes {$responseStart}-{$responseEnd}/{$responseSize}";
					}
				}
				
				$response = Response::download($storagePathFull, $filename, $responseHeaders, 'inline');
				
				//fclose($responseStream);
				
				return $response;
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
