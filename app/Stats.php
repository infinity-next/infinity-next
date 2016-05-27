<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'stats';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'stats_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stats_time', 'board_uri', 'stats_type', 'counter'];

    /**
     * Columnns which should automatically be converted to Carbon objects.
     *
     * @var array
     */
    public $dates = ['stats_time'];

    /**
     * Determines if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    public function uniques()
    {
        return $this->hasMany('\App\StatsUnique', 'stats_id');
    }
}
