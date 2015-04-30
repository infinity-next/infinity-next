<?php namespace App\Http\Controllers;

use App\Payment;
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
		$donors      = Payment::where('amount', '>', '0')->orderBy('amount', 'desc')->get();
		
		$donorGroups = [
			'uber'   => [],
			'plat'   => [],
			'gold'   => [],
			'silver' => [],
			'bronze' => [],
		];
		$donorWeights = [
			'uber'   => 25,
			'plat'   => 20,
			'gold'   => 15,
			'silver' => 10,
			'bronze' => 10,
		];
		
		foreach ($donors as $donor)
		{
			if ($donor->amount >= 10000)
			{
				$donorGroups['uber'][] = $donor;
			}
			else if ($donor->amount >= 5000)
			{
				$donorGroups['plat'][] = $donor;
			}
			else if ($donor->amount >= 1800)
			{
				$donorGroups['gold'][] = $donor;
			}
			else if ($donor->amount >= 1200)
			{
				$donorGroups['silver'][] = $donor;
			}
			else
			{
				$donorGroups['bronze'][] = $donor;
			}
		}
		
		
		return View::make('contribute', [
			"donors" => $donorGroups,
			"donorWeight" => $donorWeights,
		]);
	}
}