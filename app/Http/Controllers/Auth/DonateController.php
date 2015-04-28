<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DonationRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class DonateController extends Controller {
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
	}
	
	/*
	|--------------------------------------------------------------------------
	| Donate Controller
	|--------------------------------------------------------------------------
	|
	| When a user wants to donate money, they can do so through this form.
	| Donations can either be handled one-time (and anonymously)
	| or through a cyclic billing system that they set up.
	|
	*/
	
	/**
	 * Opens the password reset form.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return view('content.donate');
	}
	
	public function postIndex(DonationRequest $request)
	{
		$user  = $this->auth->user();
		
		if ($user)
		{
			$this->validate($request, [
				'stripeToken'  => 'required',
				'payment'      => 'required|in:once,monthly',
				
				'amount'       => 'required_if:payment,once',
				
				'subscription' => 'required_if:payment,monthly|in:monthly-three,monthly-six,monthly-twelve,monthly-eighteen',
			]);
			
			
			$input = Input::all();
			
			switch ($input['payment'])
			{
				case "once":
					$user->charge($input['amount'] * 100, [
						'description'   => "larachan dev donation",
						'source'        => $input['stripeToken'],
						'receipt_email' => $user->email,
					]);
				break;
				
				case "monthly":
					$user->subscription($input['subscription'])->create($input['stripeToken'], [
						'description'   => "larachan dev donation",
						'email'         => $user->email,
						'source'        => $input['stripeToken'],
					]);
				break;
			}
		}
		
		return view('content.donate');
	}
	
}