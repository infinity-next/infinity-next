<?php namespace App\Http\Controllers;

use App\Payment;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\View;

use Carbon\Carbon;

class PageController extends Controller {
	
	/*
	|--------------------------------------------------------------------------
	| Page Controller
	|--------------------------------------------------------------------------
	|
	| This controller distributes static content.
	|
	*/
	
	const VIEW_CONTRIBUTE = "pages.contribute";
	
	/**
	 * Show the board index for the user.
	 *
	 * @return Response
	 */
	public function getContribute()
	{
		$devCarbon = new Carbon("05/20/15");
		
		$devTimeSum  = 0;
		$devTimer    = "0 hours";
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
			// Add the amount to the dev timer.
			$devTimeSum += $donor->amount;
			
			// Sort each donor into groups.
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
		
		// Determine the time in a literal string.
		$devHours = 0;
		$devDays  = 0;
		$devInflation = (24 * 7) / (8 * 5); // This inflation factor will make the dev timer reflect off hours too, on the assumption of a 40 hour work week.
		
		$devTime   = (($devTimeSum / 100) / (float) env('CONTRIB_HOUR_COST', 10)) * $devInflation;
		$devCarbon = $devCarbon->addHours($devTime);
		
		$devDays   = $devCarbon->diffInDays();
		$devHours  = $devCarbon->diffInHours() - ($devDays * 24);
		
		if ($devHours > 0 || $devDays > 0)
		{
			$devTimer = "";
			
			if ($devDays > 0)
			{
				if ($devDays != 1)
				{
					$devTimer .= "{$devDays} days";
				}
				else
				{
					$devTimer .= "{$devDays} day";
				}
				
				if ($devHours > 0)
				{
					$devTimer .= " and ";
				}
			}
			
			if ($devHours > 0)
			{
				if ($devHours != 1)
				{
					$devTimer .= "{$devHours} hours";
				}
				else
				{
					$devTimer .= "{$devHours} hour";
				}
			}
		}
		
		return $this->view(static::VIEW_CONTRIBUTE, [
			"devCarbon"   => $devCarbon,
			"devTimer"    => $devTimer,
			"donations"   => $devTimeSum,
			"donors"      => $donorGroups,
			"donorWeight" => $donorWeights,
		]);
	}
}