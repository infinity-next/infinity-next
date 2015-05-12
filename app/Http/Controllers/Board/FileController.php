<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\FileStorage;
use App\FileAttachment;
use App\Post;

use App\Http\Controllers\MainController;
use Illuminate\Http\Request;

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
			$FileStorage = FileStorage::getHash($hash);
			
			if ($FileStorage instanceof FileStorage && Storage::exists("attachments/{$hash}"))
			{
				$responseFile = Storage::get("attachments/{$hash}");
				
				$response = Response::make($responseFile, 200);
				$response->header('content-type', $FileStorage->mime);
				$response->header('content-disposition', "inline");
				$response->header('filename', $filename);
				
				return $response;
			}
		}
		
		return abort(404);
	}
	
}
