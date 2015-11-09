<?php namespace App;

use App\Contracts\PermissionUser as PermissionUser;
use App\Support\IP;
use Illuminate\Database\Eloquent\Model;
use Request;

class BanAppeal extends Model {
	
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
	
	/**
	 * Returns any appeals that are open which this user can manage.
	 *
	 * @return Collection  of \App\BanAppeal
	 */
	public static function getAppealsFor(PermissionUser $user)
	{
		return static::whereHas('ban', function($query) use ($user) {
				$query->whereActive();
				$query->whereIn('board_uri', $user->canManageAppealsIn());
			})
			->with('ban')
			->whereOpen()
			->get();
	}
	
	/**
	 * Gets our binary value and unwraps it from any stream wrappers.
	 *
	 * @param  mixed  $value
	 * @return IP
	 */
	public function getAppealIpAttribute($value)
	{
		return new IP($value);
	}
	
	/**
	 * Sets our binary value and encodes it if required.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function setAppealIpAttribute($value)
	{
		$this->attributes['appeal_ip'] = (new IP($value))->toSQL();
	}
	
	public function scopeWhereAppealIP($query, $ip)
	{
		return $query->where('appeal_ip', (new IP));
	}
	
	public function scopeIpString($query, $ip)
	{
		return $query->whereAppealIP($ip);
	}
	
	public function scopeIpBinary($query, $ip)
	{
		return $query->whereAppealIP($ip);
	}
	
	public function scopeWhereOpen($query)
	{
		return $query->whereNull('approved');
	}
	
}
