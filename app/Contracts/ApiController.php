<?php namespace App\Contracts;

interface ApiController {
	
	/**
	 * Takes input and provides a response.
	 * This will switch between a standard JSON output and a Messenger respnse
	 * depending on request parameters.
	 *
	 * @param  mixed  $data  Input data.
	 * @return Illuminate\Http\JsonResponse|App\Http\MessengerResponse
	 */
	public function apiResponse($data = null, $status = 200, $headers = array());
	
}
