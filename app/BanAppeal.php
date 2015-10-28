<?php namespace App;

use App\Support\IP\CIDR as CIDR;
use Request;

class Ban extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'ban_appeals';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */	
	protected $primaryKey = 'ban_appeal_id';
	
	/**
	 * Attributes which are automatically sent through a Carbon instance on load.
	 *
	 * @var array
	 */
	protected $dates = ['created_at', 'updated_at'];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['ban_id', 'created_at', 'updated_at', 'appeal_ip', 'appeal_text', 'seen', 'approved', 'mod_id',];
	
	
	public function ban()
	{
		return $this->belongsTo('\App\Ban', 'ban_id');
	}
	
	public function scopeIpString($query, $ip)
	{
		return $query->ipBinary(inet_pton($ip));
	}
	
	public function scopeIpBinary($query, $ip)
	{
		return $query->where('appeal_ip', $ip);
	}
}
