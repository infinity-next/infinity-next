<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
	
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
	protected $fillable = ['customer_id', 'attribution', 'ip', 'amount', 'subscription'];
	
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
}