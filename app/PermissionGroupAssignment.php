<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionGroupAssignment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permission_group_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['permission_id', 'permission_group_id', 'display_order'];

    /**
     * Indicates if Laravel should set created_at and updated_at timestamps.
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var array
     */
    public $incrementing = false;

    public function option()
    {
        return $this->belongsTo('\App\Permission', 'permission_id');
    }

    public function group()
    {
        return $this->belongsTo('\App\PermissionGroup', 'permission_group_id');
    }
}
