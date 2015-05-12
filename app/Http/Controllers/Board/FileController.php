<?php namespace App\Http\Controllers\Board;

use App\Board;
use App\Post;

use App\Http\Controllers\MainController;
use Illuminate\Http\Request;
use Illuminate\Http\Redirect;

use File;
use Storage;
use Request;
use Redirect;

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
	public function getFile(Request $request, Board $board, $hash = false, $filename = false, $prefix="/static/")
	{
		if ($hash !== false && $filename !== false)
		{
			// redirect to static image directory
			var $split = explode($filename, ".");
			var $ext = end($split);
			var $url = "${prefix}{$hash}.{$ext}";
			return redirect($url);
		}
		
		return abort(404);
	}
	
}
