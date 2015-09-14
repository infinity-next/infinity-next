<?php namespace App\Validators;

use App\FileStorage;
use Illuminate\Validation\Validator;

class FileValidator extends Validator
{
	
	public function validateVideo($attribute, $value, $parameters)
	{
		
	}
	
	public function validateFileIntegrity($attribute, $file, $parameters)
	{
		if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
		{
			switch ($file->getClientMimeType())
			{
				// For some reason, MP3 files routinely get scanned as octet-streams.
				// Attempt to evaluate it as music or a video.
				case "application/octet-stream" :
				case "audio/mpeg" :
				case "audio/mp3"  :
				case "video/mp4"  :
				case "video/flv"  :
				case "video/webm" :
					return FileStorage::probe($file);
				
				case "application/x-shockwave-flash" :
					// This is much slower than exif_imagetype but much more reliable with flash files.
					return getimagesize($file->getPathname())['mime'] == "application/x-shockwave-flash";
				
				case "image/x-ms-bmp" :
					return exif_imagetype($file->getPathname()) == IMAGETYPE_BMP;
				
				case "image/gif"  :
					return exif_imagetype($file->getPathname()) == IMAGETYPE_GIF;
				
				case "image/jpeg" :
				case "image/jpg"  :
					return exif_imagetype($file->getPathname()) == IMAGETYPE_JPEG;
				
				case "image/png"  :
					return exif_imagetype($file->getPathname()) == IMAGETYPE_PNG;
				
				case "image/svg" :
				case "image/svg+xml" :
					try
					{
						$dom = new \DOMDocument;
						$dom->Load($file->getPathname());
						
						if ($dom->getElementsByTagName('script')->length > 0)
						{
							return false;
						}
						
						return $dom->saveXML() !== false;
					}
					catch (\Exception $error)
					{
						return false;
					}
					
				// Things we allow but can't validate.
				case "application/epub+zip" :
				case "application/pdf"      :
					return true;
				
				default :
					return false;
			}
		}
		
		return false;
	}
	
}
