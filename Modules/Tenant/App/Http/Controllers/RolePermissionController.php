<?php

namespace Modules\Tenant\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\{Role, Permission};
use DB;
use Illuminate\Contracts\Database\Eloquent\Builder;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')->whereNot('name', 'admin')->latest('created_at')->get();
        

        
        if(isset($roles) && !empty($roles)){
            foreach ($roles as $key => &$value) {
                $value->permission = collect($value->permissions)->pluck('uuid')->toArray();
                unset($value->permissions);
            }
        }

        return response()->json(['data'=>$roles]);
    }

    /**
     * Display a listing of the resource.
     */
    public function indexP(Request $request)
    {
        $permission = Permission::latest('created_at')->get();

        return response()->json(['data'=>$permission]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:100|unique:roles,name'
        ]);

        DB::beginTransaction();
        try {

            $team = Role::create([
                'name' => $request->name,
                'guard_name' => "sanctum"
            ]);

            DB::commit();
            return response()->json(['role' => $team], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->permission = $role->permissions->pluck('uuid');
            unset($role->permissions);

            return response()->json(["role"=>$role, 'permissions'=>  Permission::latest('created_at')->get()], 200);
        } catch (\Throwable $th) {
            return response()->json(["message"=> "Error", "error" => $th->getMessage()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:2|max:100|unique:users,email,'.$id
        ]);

        DB::beginTransaction();
        try {

            $role = Role::findOrFail($id);
            $role->name = $request->name;
            $role->save();

            DB::commit();
            return response()->json(['role' => $role], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    /**
     * Assign permission the specified resource in storage.
     */
    public function assignPermission(Request $request, $id)
    {
        $request->validate([
            'permission_name' => 'required',
            'permission_status' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);
            
            if($request->permission_status == true){
                $role->givePermissionTo($request->permission_name);
            } else {
                $role->revokePermissionTo($request->permission_name);
            }

            DB::commit();
            return response()->json(['status' => 'Permission Updated'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
