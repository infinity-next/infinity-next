<?php

namespace App\Validators;

use App\FileStorage;
use InfinityNext\Sleuth\FileSleuth;
use DB;

class FileValidator
{
    public function validateMd5($attribute, $value, $parameters)
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/i', $value);
    }

    public function validateSha256($attribute, $value, $parameters)
    {
        return (bool) preg_match('/^[A-Fa-f0-9]{64}$/', $value);
    }

    public function validateFileName($attribute, $value, $parameters)
    {
        return preg_match("/^[^\/\?\*;{}\\\]+\.[^\/\?\*;{}\\\]+$/", $value)
            && !preg_match("/^(nul|prn|con|lpt[0-9]|com[0-9])(\.|$)/i", $value);
    }

    public function validateFileIntegrity($attribute, $file, $parameters)
    {
        if ($file instanceof \Illuminate\Http\UploadedFile) {
            $ext = $file->guessExtension();

            $sleuth = new FileSleuth($file);
            $detective = $sleuth->check($file->getRealPath(), $ext);

            if ($detective === false) {
                $detective = $sleuth->check($file->getRealPath());
            }

            if ($detective !== false) {
                $file->case = $detective;

                return true;
            }
        }

        return false;
    }

    public function validateFileNew($attribute, $value, $parameters)
    {
        return (int)FileStorage::where('hash', $value)->pluck('upload_count')->first() == 0;
    }

    public function validateFileOld($attribute, $value, $parameters)
    {
        return (int)FileStorage::where('hash', $value)->pluck('upload_count')->first() > 0;
    }
}
