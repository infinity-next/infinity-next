<?php namespace App\Http\Controllers;

use App\Payment;
use App\Page;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class PageController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Page Controller
	|--------------------------------------------------------------------------
	|
	| This controller distributes static content.
	|
	*/

	const VIEW_CONTRIBUTE = "pages.contribute";

	public static $ContributeProjectStart = "05/05/15";
	public static $ContributePublicStart  = "05/20/15";
	public static $ContributeDevInflation = 4.2; // (24 * 7) / (8 * 5)

	/**
	 * Show the board index for the user.
	 *
	 * @return Response
	 */
	public function getContribute()
	{
		$devStart  = new Carbon( static::$ContributeProjectStart );
		$devCarbon = new Carbon( static::$ContributePublicStart );

		$devTimeSum  = 0;
		$devTimer    = "0 hours";
		$donorGroups = Payment::getDonorGroups();

		$donorWeights = [
			'uber'   => 25,
			'plat'   => 20,
			'gold'   => 15,
			'silver' => 10,
			'bronze' => 10,
		];

		foreach ($donorGroups as $donorGroup)
		{
			foreach ($donorGroup as $donor)
			{
				// Add the amount to the dev timer.
				$devTimeSum += $donor->amount;
			}
		}

		// Determine the time in a literal string.
		$devHours = 0;
		$devDays  = 0;
		$devInflation = static::$ContributeDevInflation; // This inflation factor will make the dev timer reflect off hours too, on the assumption of a 40 hour work week.

		$devTime   = (($devTimeSum / 100) / (float) env('CONTRIB_HOUR_COST', 10)) * $devInflation;
		$devCarbon->addHours($devTime);

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
			"devCarbon"    => $devCarbon,
			"devTimer"     => $devTimer,
			"devStart"     => $devStart,
			"donations"    => $devTimeSum,
			"donors"       => $donorGroups,
			"donorWeight"  => $donorWeights,
		]);
	}

	/**
	 * Show a single static document.
	 *
	 * @return Illuminate\Http\Response
	 */
	public function getPage(Page $page)
	{
		return $this->view('board.page', [
			'page' => $page,
		]);
	}
}
