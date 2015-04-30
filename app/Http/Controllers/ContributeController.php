<?php namespace App\Http\Controllers;

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\View;

class ContributeController extends MainController {
	
	/*
	|--------------------------------------------------------------------------
	| Contribute Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles any requests regarding the Larachan development.
	| Stripe and other merchant services are included in this.
	|
	*/
	
	/**
	 * Show the board index for the user.
	 * This is usually the last few threads.
	 *
	 * @return Response
	 */
	public function index()
	{
		return View::make('contribute');
	}
	
	/**
	 * Opens the donation page.
	 */
	public function donate()
	{
		return View::make('content.donate');
	}
}