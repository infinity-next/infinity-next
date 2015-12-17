<?php namespace App\Http\Controllers\Content;

use App\Board;
use App\FileStorage;
use App\FileAttachment;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

use File;
use Input;
use Settings;
use Storage;
use Request;
use Response;
use Validator;

use Event;
use App\Events\AttachmentWasModified;

class ImageController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Image Controller
	|--------------------------------------------------------------------------
	|
	| Handles requests for static image content.
	| 
	*/
	
	const VIEW_VERIFY      = "board.verify";
	const VIEW_VERIFY_PASS = "board.verify.password";
	const VIEW_VERIFY_MOD  = "board.verify.mod";
	
	/**
	 * Delete a post's attachment.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @return Response
	 */
	public function getDeleteAttachment(Board $board, FileAttachment $attachment)
	{
		if (!$attachment->exists)
		{
			return abort(404);
		}
		
		if (!$this->user->canDeleteGlobally()
			&& !$this->user->canDeleteLocally($board)
			&& !$this->user->canDeletePostWithPassword($board)
		)
		{
			return abort(403);
		}
		
		$scope = [
			'board' => $board,
			'mod'   => $this->user->canDeleteGlobally() || $this->user->canDeleteLocally($board),
		];
		
		return $this->view(static::VIEW_VERIFY, $scope);
	}
	
	/**
	 * Toggle a post's spoiler status.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @return Response
	 */
	public function getSpoilerAttachment(Board $board, FileAttachment $attachment)
	{
		if (!$attachment->exists)
		{
			return abort(404);
		}
		
		if (!$this->user->canSpoilerAttachmentGlobally()
			&& !$this->user->canSpoilerAttachmentLocally($board)
			&& !$this->user->canSpoilerAttachmentWithPassword($board)
		)
		{
			return abort(403);
		}
		
		$scope = [
			'board' => $board,
			'mod'   => $this->user->canSpoilerAttachmentGlobally() || $this->user->canSpoilerAttachmentLocally($board),
		];
		
		return $this->view(static::VIEW_VERIFY, $scope);
		
		
		$attachment->is_spoiler = !$attachment->is_spoiler;
		$attachment->save();
		
		Event::fire(new AttachmentWasModified($attachment));
		
		return redirect()->back();
	}
	
	/**
	 * Delete a post's attachment.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @return Response
	 */
	public function postDeleteAttachment(Board $board, FileAttachment $attachment)
	{
		if (!$attachment->exists)
		{
			return abort(404);
		}
		
		$input = Input::all();
		
		$validator = Validator::make($input, [
			'scope'    => "required|string|in:other,self",
			'confirm'  => "boolean|required_if:scope,other",
			'password' => "string|required_if:scope,self"
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withInput($input)
				->withErrors($validator->errors());
		}
		
		if ($input['scope'] == "other")
		{
			if ($this->user->canDeleteGlobally() || $this->user->canDeleteLocally($board))
			{
				$this->log('log.attachment.delete', $attachment->post, [
					"board_uri" => $attachment->post->board_uri,
					"board_id"  => $attachment->post->board_id,
					"post_id"   => $attachment->post->post_id,
					"file"      => $attachment->storage->hash,
				]);
			}
			else
			{
				abort(403);
			}
		}
		else if ($input['scope'] == "self")
		{
			if ($this->user->canDeletePostWithPassword($board))
			{
				if (!$attachment->post->checkPassword($input['password']))
				{
					return redirect()
						->back()
						->withInput($input)
						->withErrors([
							'password' => \Lang::trans('validation.password', [
								'attribute' => "password",
							])
						]);
				}
			}
		}
		
		$attachment->is_deleted = true;
		$attachment->save();
		
		Event::fire(new AttachmentWasModified($attachment));
		
		
		return redirect($attachment->post->getURL());
	}
	
	/**
	 * Delete a post's attachment.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @return Response
	 */
	public function postSpoilerAttachment(Board $board, FileAttachment $attachment)
	{
		if (!$attachment->exists)
		{
			return abort(404);
		}
		
		$input = Input::all();
		
		$validator = Validator::make($input, [
			'scope'    => "required|string|in:other,self",
			'confirm'  => "boolean|required_if:scope,other",
			'password' => "string|required_if:scope,self"
		]);
		
		if (!$validator->passes())
		{
			return redirect()
				->back()
				->withInput($input)
				->withErrors($validator->errors());
		}
		
		if ($input['scope'] == "other")
		{
			if ($this->user->canDeleteGlobally() || $this->user->canDeleteLocally($board))
			{
				$this->log(
					!$attachment->is_spoiler ? 'log.attachment.spoiler' : 'log.attachment.unspoiler',
					$attachment->post,
					[
						"board_uri" => $attachment->post->board_uri,
						"board_id"  => $attachment->post->board_id,
						"post_id"   => $attachment->post->post_id,
						"file"      => $attachment->storage->hash,
					]
				);
			}
			else
			{
				abort(403);
			}
		}
		else if ($input['scope'] == "self")
		{
			if ($this->user->canDeletePostWithPassword($board))
			{
				if (!$attachment->post->checkPassword($input['password']))
				{
					return redirect()
						->back()
						->withInput($input)
						->withErrors([
							'password' => \Lang::trans('validation.password', [
								'attribute' => "password",
							])
						]);
				}
			}
		}
		
		$attachment->is_spoiler = !$attachment->is_spoiler;
		$attachment->save();
		
		Event::fire(new AttachmentWasModified($attachment));
		
		
		return redirect($attachment->post->getURL());
	}
	
	/**
	 * Delivers an image.
	 *
	 * @param  \App\FileAttachment  $attachment
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
		
		if (is_string($hash) && is_string($filename))
		{
			$FileStorage     = FileStorage::getHash($hash);
			$storagePath     = !$thumbnail ? $FileStorage->getPath()     : $FileStorage->getPathThumb();
			$storagePathFull = !$thumbnail ? $FileStorage->getFullPath() : $FileStorage->getFullPathThumb();
			$cacheTime       =  31536000; /// 1 year
			
			if (is_link($storagePathFull))
			{
				if (!is_readable($storagePathFull))
				{
					abort(500, "Symlink file is unreadable.");
				}
				
				$storageExists = file_exists($storagePathFull);
				$storageSize = filesize($storagePathFull);
			}
			else
			{
				$storageExists = Storage::exists($storagePath);
				$storageSize = filesize($storagePath);
			}
			
			if ($FileStorage instanceof FileStorage && $storageExists)
			{
				ini_set("zlib.output_compression", "Off");
				
				$responseSize    = $storageSize;
				$responseCode    = 200;
				$responseHeaders = [
					'Cache-Control'        => "public, max-age={$cacheTime}, pre-check={$cacheTime}",
					'Expires'              => gmdate(DATE_RFC1123, time() + $cacheTime),
					'Last-Modified'        => gmdate(DATE_RFC1123, File::lastModified($storagePathFull)),
					'Content-Disposition'  => Request::get('disposition', "inline"),
					//'Content-Disposition'  => "attachment; filename={$filename}",
					'Content-Length'       => $responseSize,
					'Content-Type'         => $FileStorage->mime,
					'Filename'             => urldecode($filename),
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
				$xSendFile = false;
				
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
	 * Delivers a file from an attachment.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @param  string  $filename
	 */
	public function getImageFromAttachment(FileAttachment $attachment, $filename = false)
	{
		return $this->getImage($attachment->storage->hash, $filename);
	}
	
	/**
	 * Delivers a file from a hash.
	 *
	 * @param  string  $hash
	 * @param  string  $filename
	 */
	public function getImageFromHash($hash = false, $filename = false)
	{
		return $this->getImage($hash, $filename, false);
	}
	
	/**
	 * Delivers a file's thumbnail by rerouting the request to getFile with an optional parameter set.
	 *
	 * @param  \App\FileAttachment  $attachment
	 * @param  $string  $filename
	 * @return Response
	 */
	public function getThumbnailFromAttachment(FileAttachment $attachment, $filename = false)
	{
		return $this->getImage($attachment->storage->hash, $filename, true);
	}
	
	/**
	 * Delivers a file from a hash.
	 *
	 * @param  string  $hash
	 * @param  string  $filename
	 */
	public function getThumbnailFromHash($hash = false, $filename = false)
	{
		return $this->getImage($hash, $filename, true);
	}
	
}
