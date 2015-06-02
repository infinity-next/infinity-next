<?php namespace App\Validators;

use Illuminate\Validation\Validator;

class ComparisonValidator extends Validator
{
	public function validateGreaterThan($attribute, $value, $parameters)
	{
		$value = (int) $value;
		
		foreach ($parameters as $parameter)
		{
			if (isset($this->data[$parameter]) && (int) $this->data[$parameter] > $value)
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function validateLessThan($attribute, $value, $parameters)
	{
		$value = (int) $value;
		foreach ($parameters as $parameter)
		{
			if (isset($this->data[$parameter]) && (int) $this->data[$parameter] < $value)
			{
				return false;
			}
		}
		
		return true;
	}
}
