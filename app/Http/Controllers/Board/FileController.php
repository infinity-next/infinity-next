<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;

use App\Http\Controllers\MainController;
use Illuminate\Http\Request;

use File;
use Storage;

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
	 * redirects to a file.
	 *
	 * TODO: make prefix configurable
	 *
	 * @param  \Illuminate\Http\Request	 $request
	 * @param  \App\Board $board
	 * @param  \App\FileStorage->hash $hash
	 * @param  \App\FileAttachment->hash $filename
	 * @param  string $prefx
	 * @return Response
	 */
	public function getFile(Request $request, Board $board, $hash = false, $filename = false, $prefix="/uploads/")
	{
		if ($hash !== false && $filename !== false)
		{
			// redirect to static image directory
			$split = explode($filename, ".");
			$ext = end($split);
			//$url = "${prefix}{$hash}.{$ext}";
            $url = $filename;
			return redirect($url);
		}
		
		return abort(404);
	}
	
}
