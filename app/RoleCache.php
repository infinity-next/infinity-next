<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleCache extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'role_cache';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'role_cache_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'board_uri', 'value'];

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

    public function user()
    {
        return $this->belongsTo('\App\User', 'user_id');
    }

    public function role()
    {
        return $this->belongsTo('\App\Role', 'role_id');
    }

    /**
     * @static
     *
     * @param array|Collection $roles        Roles being added.
     * @param array            $routes       Routes which we follow to correctly build with.
     * @param array|Collection $parentRoles  Roles which may be inherited, usually system.
     * @param array|Collection $userRoles    Roles which are directly assigned to the user.
     * @param array            &$permissions The permission mask.
     *
     * @return array Permission mask.
     */
    public static function addRolesToPermissions($roles, $routes, $parentRoles, $userRoles, &$permissions)
    {
        // With our roles fresh off out the db, we can now begin to assemble the masks.
        // Loop through each route again.
        foreach ($routes as $branch => $roleGroups) {
            // Loop through each role.
            foreach ($roles as $roleIndex => $role) {
                // Check to see if it's either directly assigned to us or in the mask's route.
                if (in_array($role->role, $roleGroups) || in_array($role->role_id, $userRoles)) {
                    // This role IS applicable to this branch.

                    // Create a new array for this board if required.
                    if (!isset($permissions[$branch][$role->board_uri])) {
                        $permissions[$branch][$role->board_uri] = [];
                    }

                    // Loop through each inherited permission and add them to the pot.
                    // Note: It may be a good idea to instead fetch this and inline it by weight.
                    if ($role->inherit_id) {
                        $inherits = $parentRoles[$role->inherit_id];

                        foreach ($inherits->permissions as $permission) {
                            $permissions[$branch][$role->board_uri][$permission->permission_id] = (bool) $permission->pivot->value;
                        }
                    }

                    // Loop through each role's permission and set them on the respective jurisdiction.
                    foreach ($role->permissions as $permission) {
                        $permissions[$branch][$role->board_uri][$permission->permission_id] = (bool) $permission->pivot->value;
                    }

                    // Additionally, if our permission is set on the global level, we must also go into each
                    // lesser jurisdiction and unset their rule because it no longer matters.
                    if (is_null($role->board_uri)) {
                        foreach ($permissions[$branch] as $board_uri => $boardWeights) {
                            if ((string) $board_uri == '') {
                                continue;
                            }

                            foreach ($role->permissions as $permission) {
                                unset($permissions[$branch][$board_uri][$permission->permission_id]);
                            }

                            if (!count($permissions[$branch][$board_uri])) {
                                unset($permissions[$branch][$board_uri]);
                            }
                        }
                    }
                }
            }
        }

        return $permissions;
    }
}
