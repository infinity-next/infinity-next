<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardSetting extends Model
{
    use \App\Traits\EloquentBinary;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'board_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_setting_id',
        'option_name',
        'board_uri',
        'option_value',
        'is_locked',
    ];

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'board_setting_id';

    /**
     * Determines if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    public function option()
    {
        return $this->belongsTo('\App\Option', 'option_name');
    }

    public function board()
    {
        return $this->belongsTo('\App\Board', 'board_uri');
    }

    /**
     * Is this setting locked? (Editable only by users with special permissions).
     *
     * @return bool
     */
    public function isLocked()
    {
        return (bool) $this->is_locked;
    }

    /**
     * Gets our option value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getOptionValueAttribute($value)
    {
        $value = binary_unsql($value);

        if (isset($this->attributes['data_type']) && $this->attributes['data_type'] == 'array') {
            $value = json_decode($value, true);
        }

        return $value;
    }

    /**
     * Sets our option value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setOptionValueAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['option_value'] = binary_sql($value);
    }
}
