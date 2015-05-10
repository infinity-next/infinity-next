<?php namespace App\Http\Controllers\Auth;

use App\Payment;
use App\User;
use App\Http\Controllers\Auth\AuthenticatesAndRegistersUsers;
use App\Http\Controllers\Auth\CpController;
use App\Http\Requests\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class DonateController extends CpController {
	
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
		$donated = 0;
		$user    = $this->auth->user();
		
		if ($user)
		{
			$donated = $user->payments()->sum('amount');
		}
		
		return View::make('content.donate', [
			'donated' => $donated,
			'cycles'  => DonationRequest::getCycles(),
			'amounts' => DonationRequest::getOptions(),
		]);
	}
	
	/**
	 * Handles a payment.
	 *
	 * @param  App/Http/Requests/DonationRequest  $request
	 * @return Response
	 */
	public function postIndex(DonationRequest $request)
	{
		$errors   = [];
		$fakeUser = false;
		
		$user     = $this->auth->user();
		$input    = Input::all();
		
		// Create a dummy account if we're not authenticated.
		if (!$user)
		{
			$fakeUser = true;
			$user = new User();
		}
		
		// Build our \App\Payment model.
		$payment = [
			'customer'     => ($fakeUser ? NULL : $user->id ),
			'attribution'  => $input['attribution'],
			'ip'           => $request->getClientIp(),
			'amount'       => $input['amount'] * 100,
			'currency'     => "usd",
			'subscription' => NULL,
		];
		
		
		// Handle input depending on the type of payment.
		// Stripe does subscriptions and charges differently.
		switch ($input['payment'])
		{
			case "once":
				$tx = [
					'description'   => "infinity dev donation",
					'source'        => $input['stripeToken'],
					'receipt_email' => $input['email'],
				];
				
				$receipt = $user->charge($payment['amount'], $tx);
			break;
			
			case "monthly":
				$tx = [
					'description'   => "larachan dev donation",
					'source'        => $input['stripeToken'],
					'email'         => $input['email'],
				];
				
				$receipt = $user->subscription("monthly-{$input['amount']}")->create($input['stripeToken'], $tx);
				$payment['subscription'] = "monthly-{$input['amount']}";
			break;
		}
		
		if ($receipt !== false)
		{
			// Record our payment.
			// This stores no identifying information,
			// besides an optional user ID.
			Payment::create($payment)->save();
		}
		else
		{
			$errors[] = "Your card failed to process and has not been charged.";
		}
		
		if ($fakeUser === true)
		{
			$user->delete();
		}
		
		if ($request->ajax())
		{
			return response()->json([
				'amount'  => count($errors) ? false : "\$" . ($payment['amount'] / 100),
				'errors'  => $errors,
			]);
		}
		else
		{
			return view('content.donate');
		}
	}
	
}