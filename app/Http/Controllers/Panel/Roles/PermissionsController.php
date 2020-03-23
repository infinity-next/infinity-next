<?php

namespace App\Http\Controllers\Panel\Roles;

use App\Permission;
use App\PermissionGroup;
use App\Role;
use App\RolePermission;
use App\Http\Controllers\Panel\PanelController;
use Input;
use Event;
use App\Events\RoleWasModified;

class PermissionsController extends PanelController
{
    const VIEW_PERMISSIONS = 'panel.roles.permissions.edit';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.users';

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index(Role $role)
    {
        if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions()) {
            return abort(403);
        }

        $permissionGroups = PermissionGroup::orderBy('display_order', 'asc')->withPermissions()->get();
        $permissionGroups = $permissionGroups->filter(function ($group) {
            $permissions = $group->permissions->filter(function ($permission) {
                return $this->user->can($permission);
            });

            $group->setRelation('permissions', $permissions);

            return $permissions->count();
        });

        return $this->view(static::VIEW_PERMISSIONS, [
            'role' => $role,
            'groups' => $permissionGroups,
        ]);
    }

    public function patch(Role $role)
    {
        if (!$this->user->canAdminRoles() || !$this->user->canAdminPermissions()) {
            return abort(403);
        }

        $input = Input::all();
        $permissions = Permission::all();
        $rolePermissions = [];
        $nullPermissions = [];

        foreach ($permissions as $permission) {
            if ($this->user->can($permission->permission_id)) {
                $nullPermissions[] = $permission->permission_id;

                foreach ($input['permission'] as $permission_id => $permission_value) {
                    $permission_id = str_replace('_', '.', $permission_id);

                    if ($permission->permission_id == $permission_id) {
                        switch ($permission_value) {
                            case 'allow':
                            case 'deny':
                                $rolePermissions[] = [
                                    'role_id' => $role->role_id,
                                    'permission_id' => $permission_id,
                                    'value' => $permission_value == 'allow',
                                ];
                            break;
                        }

                        break;
                    }
                }
            }
        }

        RolePermission::where(['role_id' => $role->role_id])->whereIn('permission_id', $nullPermissions)->delete();

        RolePermission::insert($rolePermissions);

        $permission_groups = PermissionGroup::withPermissions()->get();


        Event::dispatch(new RoleWasModified($role));

        return $this->view(static::VIEW_PERMISSIONS, [
            'role' => $role,
            'groups' => $permission_groups,
        ]);
    }
}
