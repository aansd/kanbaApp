<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    protected function getUserPermissions($user)
    {
        return $user
            ->role()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');
    }

    public function viewUserAndRole($user)
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('view-users-and-roles')) {
            return true;
        }

        return false;
    }

    // public function performAsTaskOwner($user, $task)
    // {
    //     return $user->id == $task->user_id;
    // }

    
    public function manageUserRole($user)
   {
   $permissions = $this->getUserPermissions($user);

   if ($permissions->contains('manage-user-roles')) {
       return true;
   }

   return false;
   }

   public function before($user)
    {
    if ($user->role && $user->role->name == 'admin') {
        return true;
    }
    
    return null;
    }
}
