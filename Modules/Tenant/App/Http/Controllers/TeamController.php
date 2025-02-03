<?php

namespace Modules\Tenant\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Role;
use Modules\Tenant\App\Models\User;
use DB;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tenant::index');
    }

    public function create()
    {
        try {
            return response()->json(['roles' => Role::get()->pluck('name')], 200);
        } catch (\Exception $e) {
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:100',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required',
            'role' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $team = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $team->guard_name = 'sanctum';
            $team->assignRole($request->role);

            DB::commit();
            return response()->json(['user' => $request->all()], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->role = $user->roles->map(function($role){
                return $role->name;
            });
            unset($user->roles);

            return response()->json($user, 200);
        } catch (\Throwable $th) {
            return response()->json(["message"=> "Error", "error" => $th->getMessage()], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->role = $user->roles->map(function($role){
                return $role->name;
            });
            unset($user->roles);

            return response()->json(["user"=>$user, 'roles' => Role::get()->pluck('name')], 200);
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
            'name' => 'required|min:2|max:100',
            'email' => 'required|email:rfc,dns|unique:users,email,'.$id,
            'password' => 'sometimes',
            'role' => 'sometimes',
        ]);

        DB::beginTransaction();
        try {

            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if($request->password){
                $user->password = \Hash::make($request->password);
            }
            $user->save();

            if($request->role && $request->role != $user->role){
                $user->guard_name = 'sanctum';
                // $user->removeRole($user->role);
                $user->roles()->detach();
                $user->assignRole($request->role);
            }

            DB::commit();
            return response()->json(['user' => $request->all()], 201);
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
