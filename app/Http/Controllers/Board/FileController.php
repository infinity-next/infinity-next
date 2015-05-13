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
	public function getFile(Request $request, Board $board, $hash = false, $filename = false)
	{
		if ($hash !== false && $filename !== false)
		{
			$FileStorage     = FileStorage::getHash($hash);
			$storagePath     = "attachments/{$hash}";
			$storagePathFull = storage_path() . "/app/" . $storagePath;
			
			if ($FileStorage instanceof FileStorage && Storage::exists($storagePath))
			{
				$responeSize     = Storage::size($storagePath);
				$responseHeaders = [
					'Cache-Control'       => "public, max-age=315360000",
					'Content-Disposition' => "inline",
					'Content-Length'      => $responeSize,
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
	
}
