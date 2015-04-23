<?php namespace App\Http\Controllers;

use View;

class ContributeController extends Controller {
	
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
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		
	}
	
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
}