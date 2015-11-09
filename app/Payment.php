<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
	
	use \App\Traits\EloquentBinary;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'payments';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'payment_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['customer_id', 'attribution', 'payment_ip', 'amount', 'subscription'];
	
	/**
	 * API visible fields.
	 *
	 * @var array
	 */
	protected $visible = ['attribution', 'amount'];
	
	/**
	 * Disable automatic timestamp management.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	public static function boot()
	{
		parent::boot();
		
		// Setup event bindings...
		static::creating(function(Payment $payment)
		{
			$payment->setCreatedAt($payment->freshTimestamp());
			return true;
		});
	}
	
	public function customer()
	{
		return $this->belongsTo('\App\User', 'user_id', 'customer_id');
	}
	
	/**
	 * Gets our binary value and unwraps it from any stream wrappers.
	 *
	 * @param  mixed  $value
	 * @return IP
	 */
	public function getPaymentIpStartAttribute($value)
	{
		return new IP($value);
	}
	
	public static function getDonorGroups()
	{
		$donors = static::where('amount', '>', '0')->orderBy('amount', 'desc')->get();
		
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
		
		return $donorGroups;
	}
	
	/**
	 * Sets our binary value and encodes it if required.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function setPaymentIpStartAttribute($value)
	{
		$this->attributes['payment_ip'] = (new IP($value))->toSQL();
	}
	
}