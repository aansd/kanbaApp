<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $pageTitle = 'Role Lists';
        $roles = Role::all();
        return view('roles.index', ['pageTitle' => $pageTitle, 'roles' => $roles,]);
    }

    public function create()
    {
        $pageTitle = 'Add Role';
        $permissions = Permission::all();
        return view('roles.create', ['pageTitle' => $pageTitle, 'permissions' => $permissions,]);
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
       
        return view('roles.delete', ['pageTitle' => $pageTitle, 'role' => $role]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
       
        $role->delete();
        return redirect()->route('roles.index');
    }

}
