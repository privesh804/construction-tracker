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
            'email' => 'required|email:rfc,dns',
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
            return response()->json(['user' => $team], 201);
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
        return view('tenant::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tenant::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
