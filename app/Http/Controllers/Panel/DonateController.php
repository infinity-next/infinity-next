<?php namespace App\Http\Controllers\Panel;

use App\Payment;
use App\User;
use App\Http\Controllers\Panel\AuthenticatesAndRegistersUsers;
use App\Http\Controllers\Panel\PanelController;
use App\Http\Requests\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class DonateController extends PanelController {
	
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
	
	const VIEW_DONATE = "panel.donate";
	
	/**
	 * Opens the password reset form.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		$donated = 0;
		
		if (!$this->user->isAnonymous())
		{
			$donated = $this->user->payments()->sum('amount');
			$this->user->createBraintreeId();
		}
		
		return $this->view(static::VIEW_DONATE, [
			'donated' => $donated,
			'cycles'  => DonationRequest::getCycles(),
			'amounts' => DonationRequest::getOptions(),
			
			'BraintreeClientKey' => $this->user->getBraintreeId(),
		]);
	}
	
	/**
	 * Handles a payment.
	 *
	 * @param App/Http/Requests/DonationRequest $request
	 * @return Response
	 */
	public function postIndex(DonationRequest $request)
	{
		$errors   = [];
		$fakeUser = false;
		
		$user     = $this->user;
		$input    = Input::all();
		
		// Build our \App\Payment model.
		$payment = [
			'customer'     => $user->user_id,
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
				/* Stripe
				$tx = [
					'description'   => "Infinity Next Dev",
					'source'        => $input['nonce'],
					'receipt_email' => $input['email'],
				];
				*/
				
				$tx = [
					'amount' => ($payment['amount'] / 100),
					
					'paymentMethodNonce' => $input['nonce'],
					
					'options' => [
						'submitForSettlement' => true,
					],
				];
				
				$receipt = $user->charge($payment['amount']);
			break;
			
			case "monthly":
				/* Stripe
				$tx = [
					'description'   => "Infinity Next Dev",
					'source'        => $input['nonce'],
					'email'         => $input['email'],
				];
				$receipt = $user->subscription("monthly-{$input['amount']}")->create($input['nonce'], $tx);
				*/
				
				
				$tx = [
					'paymentMethodNonce' => $input['nonce'],
					'email'              => $input['email'],
				];
				
				$receipt = $user->subscription("monthly-{$input['amount']}")->create($input['nonce'], $tx);
				$payment['subscription'] = "monthly-{$input['amount']}";
			break;
		}
		
		
		if ($receipt instanceof \Braintree_Result_Error)
		{
			$errors[] = $receipt->message;
			$receipt = false;
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
		
		if ($request->ajax())
		{
			return response()->json([
				'amount'  => count($errors) ? false : "\$" . ($payment['amount'] / 100),
				'errors'  => $errors,
			]);
		}
		else
		{
			return $this->view(static::VIEW_DONATE);
		}
	}
	
}