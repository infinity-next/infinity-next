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
		$subscriptions = implode(',', array_values($this->getSubscriptions()));
		
		$rules = [
			'stripeToken'  => 'required',
			'payment'      => 'required|in:' . $cycles,
			
			'amount'       => 'required_if:payment,once|numeric|min:2.00',
			
			'subscription' => 'required_if:payment,monthly|in:' . $subscriptions,
		];
		
		if (Auth::check())
		{
			
		}
		
		return $rules;
	}
	
	public function getCycles()
	{
		return [
			"One-time payment" =>'once',
			"Monthly subscription" => 'monthly',
		];
	}
	
	public function getCyclesByID()
	{
		return array_flip($this->getCycles());
	}
	
	public function getSubscriptions()
	{
		return [
			"\$3 / month"  => 'monthly-three',
			"\$6 / month"  => 'monthly-six',
			"\$12 / month" => 'monthly-twelve',
			"\$18 / month" => 'monthly-eighteen',
		];
	}
	
	public function getSubscriptionsByID()
	{
		return array_flip($this->getSubscriptions());
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
	
	*/
	
	// OPTIONAL OVERRIDE
	public function response(array $errors)
	{
		// If you want to customize what happens on a failed validation,
		// override this method.
		// See what it does natively here: 
		// https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Http/FormRequest.php
		
		return view('content.donate');
	}
}