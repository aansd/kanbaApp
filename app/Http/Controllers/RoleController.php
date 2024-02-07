<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        $pageTitle = 'Role Lists';
        $roles = Role::all();
        if (Gate::denies('performAsTaskOwner', $roles)) {
            Gate::authorize('viewAnyRole', Role::class);
        }
        return view('roles.index', ['pageTitle' => $pageTitle, 'roles' => $roles]);
    }

    public function create()
    {
        $pageTitle = 'Add Role';
        $permissions = Permission::all();
        $this->authorize('createNewRole', Role::class);
        $roles = Role::with(['users', 'permissions'])->get();
        return view('roles.create', ['pageTitle' => $pageTitle, 'permissions' => $permissions, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'permissionIds' => ['required'],
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
            ]);
            
            $role->permissions()->sync($request->permissionIds);

            DB::commit();

            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Role';
        $role = Role::find($id);
        $permissions = Permission::all();
        if (Gate::denies('performAsTaskOwner', $role)) {
            Gate::authorize('UpdateAnyRole', Role::class);
        }
        return view('roles.edit', ['pageTitle' => $pageTitle, 'role' => $role, 'permissions' => $permissions]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'permissionIds' => ['required'],
        ]);
    
        DB::beginTransaction();
        
        try {
            $role = Role::findOrFail($id);
            if (Gate::denies('performAsTaskOwner', $role)) {
                Gate::authorize('UpdateAnyRole', Role::class);
            }
            $role->update([
                'name' => $request->name,
            ]);
    
            $role->permissions()->sync($request->permissionIds);
    
            DB::commit();
    
            return redirect()->route('roles.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete($id)
    {
        $pageTitle = 'Delete Role';
        $role = Role::find($id);
        if (Gate::denies('performAsTaskOwner', $role)) {
            Gate::authorize('deleteAnyRole', Role::class);
        }
        return view('roles.delete', ['pageTitle' => $pageTitle, 'role' => $role]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (Gate::denies('performAsTaskOwner', $role)) {
            Gate::authorize('deleteAnyRole', Role::class);
        }
        $role->delete();
        return redirect()->route('roles.index');
    }

}
