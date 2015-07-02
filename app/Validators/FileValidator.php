<?php namespace App\Validators;

use Illuminate\Validation\Validator;

class FileValidator extends Validator
{
	public function validateFileExists($attribute, $value)
	{
		return file_exists($value->getRealPath());
	}
}
