<?php namespace App\Validators;

class CaptchaValidator
{
	public function validateCaptcha($attribute, $value, $parameters)
	{
		return captcha_check($value);
	}
}
