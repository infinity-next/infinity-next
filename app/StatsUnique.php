<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatsUnique extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'stats_uniques';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'stats_bit_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stats_id', 'unique'];

    /**
     * Determines if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    public function uniques()
    {
        return $this->hasOne(Stats::class, 'stats_id');
    }
}
