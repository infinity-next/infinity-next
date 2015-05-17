<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\FileStorage;
use App\FileAttachment;
use App\Post;

use App\Http\Controllers\MainController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

use File;
use Storage;
use Response;

class FileController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| File Controller
	|--------------------------------------------------------------------------
	|
	| Handles requests for attachments.
	| 
	*/
	
	/**
	 * Delivers a file.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Board $board
	 * @param  \App\FileStorage->hash $hash
	 * @param  \App\FileAttachment->hash $filename
	 * @return Response
	 */
	public function getFile(Request $request, Board $board, $hash = false, $filename = false, $thumbnail = false)
	{
		if ($hash !== false && $filename !== false)
		{
			$FileStorage     = FileStorage::getHash($hash);
			$storagePath     = !$thumbnail ? $FileStorage->getPath()     : $FileStorage->getPathThumb();
			$storagePathFull = !$thumbnail ? $FileStorage->getFullPath() : $FileStorage->getFullPathThumb();
			
			if ($FileStorage instanceof FileStorage && Storage::exists($storagePath))
			{
				$responseSize    = Storage::size($storagePath);
				$responseHeaders = [
					'Cache-Control'       => "public, max-age=315360000",
					'Content-Disposition' => "inline",
					'Content-Length'      => $responseSize,
					'Content-Type'        => $FileStorage->mime,
					'Filename'            => $filename,
				];
				$response = Response::stream(function() use ($storagePathFull) {
					readfile($storagePathFull);
				}, 200, $responseHeaders);
				
				return $response;
			}
		}
		
		return abort(404);
	}
	
	/**
	 * Delivers a file's thumbnail by rerouting the request to getFile with an optional parameter set.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Board $board
	 * @param  \App\FileStorage->hash $hash
	 * @param  \App\FileAttachment->hash $filename
	 * @return Response
	 */
	public function getThumbnail(Request $request, Board $board, $hash = false, $filename = false)
	{
		return $this->getFile($request, $board, $hash, $filename, true);
	}
}
