<?php

namespace App;

use App\Board;
use App\User;
use App\Contracts\Auth\Permittable;
use App\Support\IP;
use App\Traits\EloquentBinary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Log extends Model
{
    use ;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'action_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['action_name', 'action_details', 'user_id', 'user_ip', 'board_uri'];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_uri');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Gets our binary value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getActionDetailsAttribute($value)
    {
        return binary_unsql($value);
    }

    public function getDetails($user = null)
    {
        if (is_null($user)) {
            $user = user();
        }

        $details = json_decode($this->action_details, true);

        foreach ($details as $detailKey => &$detailValue) {
            $methodName = Str::camel("get_log_visible_{$detailKey}");

            if (method_exists($this, $methodName)) {
                $detailValue = $this->{$methodName}($detailValue, $user);
            }
        }

        return $details;
    }

    public function getLogVisibleCapcode($capcode, $user = null)
    {
        return trans($capcode);
    }

    public function getLogVisibleIp($ip, ?Permittable $user = null)
    {
        if ($user !== null) {
            return $user->getTextForIP($ip);
        }

        return ip_less($ip);
    }

    /**
     * Gets our binary value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return IP
     */
    public function getUserIpAttribute($value)
    {
        return new IP($value);
    }

    /**
     * Sets our binary value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setActionDetailsAttribute($value)
    {
        $this->attributes['action_details'] = binary_sql($value);
    }

    /**
     * Sets our binary value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setUserIpAttribute($value)
    {
        $this->attributes['user_ip'] = (new IP($value))->toText();
    }
}
