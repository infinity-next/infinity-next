<?php namespace App\Http\Requests;

use Auth;
use View;

class DonationRequest extends Request {
	
	/**
	 * Returns validation rules for this request.
	 *
	 * @return Array (\Validation rules)
	 */
	public function rules()
	{
		$cycles = implode(',', array_values($this->getCycles()));
		$subscriptions = "monthly-" . implode(',monthly-', array_values($this->getOptions()));
		
		$rules = [
			'stripeToken'  => 'required',
			'email'        => 'required|email',
			'payment'      => 'required|in:' . $cycles,
			'amount'       => 'required|numeric|min:3',
		];
		
		if (Au
		return $rules;
	}
	
	public static function getCycles()
	{
		return [
			"One-time payment" =>'once',
			"Monthly support" => 'monthly',
		];
	}
	
	public static function getCyclesByID()
	{
		return array_flip(static::getCycles());
	}
	
	public static function getOptions()
	{
		return [
			3,
			6,
			12,
			20,
			30,
			50,
			100,
		];
	}
	
	
	/**
	 * Returns if the client has access to this form.
	 *
	 * @return Boolean
	 */
	public function authorize()
	{
		// Only allow logged in users
		// return \Auth::check();
		
		// Allows all users in
		return true;
	}
	
	/*
	// OPTIONAL OVERRIDE
	public function forbiddenResponse()
	{
		// Optionally, send a custom response on authorize failure 
		// (default is to just redirect to initial page with errors)
		// 
		// Can return a response, a view, a redirect, or whatever else
		return Response::make('Permission denied foo!', 403);
	}
	
	// OPTIONAL OVERRIDE
	public function response(array $errors)
	{
		// If you want to customize what happens on a failed validation,
		// override this method.
		// See what it does natively here: 
		// https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
		
		return View::make('content.donate', [
			'cycles'  => static::getCycles(),
			'amounts' => static::getOptions(),
		]);
	}
	
	*/
}