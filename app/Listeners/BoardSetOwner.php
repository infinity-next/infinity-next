<?php

namespace App\Listeners;

use App\Role;
use App\RoleCache;
use App\UserRole;

class BoardSetOwner extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        $board = $event->board;
        $user = $event->user;

        $user->forgetPermissions();

        $role = Role::getOwnerRoleForBoard($event->board);

        if (!$role->wasRecentlyCreated) {
            UserRole::where('role_id', $role->role_id)->delete();
        }

        $board->operated_by = $user->user_id;
        $board->save();

        return UserRole::create([
            'user_id' => $user->user_id,
            'role_id' => $role->role_id,
        ]);

    }
}
