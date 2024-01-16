<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        $pageTitle = 'Users List';
        if (Gate::allows('viewUserAndRole', User::class)) {
            $users = User::all();
        } else {
            $users = User::where('user_id', Auth::user()->id)->get();
        }
        return view('users.index', ['pageTitle' => $pageTitle, 'users' => $users]);
    }
    public function editRole($id)
    {
        $pageTitle = 'Edit User Role';
        $user = User::findOrFail($id);
        $roles = Role::all();
        if (Gate::denies('performAsTaskOwner', $user)) {
            Gate::authorize('manageUserRole', User::class);
        }
        return view('users.edit_role', [
            'pageTitle' => $pageTitle,
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function updateRole($id, Request $request)
    {
        $user = User::findOrFail($id);
        if (Gate::denies('performAsTaskOwner', $user)) {
            Gate::authorize('manageUserRole', User::class);
        }
        $user->update([
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('users.index');
    }
}
