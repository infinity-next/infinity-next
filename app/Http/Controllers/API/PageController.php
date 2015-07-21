<?php namespace App\Http\Controllers\API;

use App\Payment;

use App\Contracts\ApiController;
use App\Http\Controllers\PageController as ParentController;

use Carbon\Carbon;

class PageController extends ParentController implements ApiController {
	
	/**
	 * Show the board index for the user.
	 *
	 * @return Response
	 */
	public function getContribute()
	{  
		$devStart       = new Carbon( static::$ContributeProjectStart );
		$devCarbon      = new Carbon( static::$ContributePublicStart );
		
		
		$donors         = Payment::all();
		$donationsTotal = 0;
		
		foreach ($donors as $donor)
		{
			$donationsTotal += $donor->amount;
		}
		
		$devEnd         = clone $devCarbon;
		$devTime        = (($donationsTotal / 100) / (float) env('CONTRIB_HOUR_COST', 10)) * static::$ContributeDevInflation;
		$devEnd->addHours($devTime);
		
		return response()->json([
			'development_start'      => $devStart->toRfc2822String(),
			'development_public'     => $devCarbon->toRfc2822String(),
			'development_paid_until' => $devEnd->toRfc2822String(),
			
			'donations_total'        => $donationsTotal,
			'donors'                 => $donors->toJson(),
		]);
	}
}