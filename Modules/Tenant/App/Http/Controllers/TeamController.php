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
    protected $pagelimit = 10;
    protected $page = 1;
    protected $search = [];
    protected $sort = [];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $paginateQuery = $request->all();

        if($paginateQuery ?? false){
            $this->pagelimit = $paginateQuery['pageSize'] ?? 10;
            $this->page = $paginateQuery['pageIndex'] ?? 1;
            $this->search = $paginateQuery['searchText'] ?? "";
            $this->sort = json_decode($paginateQuery['sorting'], true) ?? [];
        }
        
        $team = User::with('roles:name');

        $columns = [
            'name',
            'email',
            'contact',
            'role',
            'status'
        ];

        if(isset($this->search) && !empty($this->search)){
            $this->pagelimit = 10;
            $this->page = 1;
            $team->where(function($query) use($columns){
                foreach ($columns as $column) {
                    if($column != 'role'){
                        $query->orWhere($column, "like", $this->search."%");
                    }
                }
                $query->orWhereHas('roles', function($subquery){
                    $subquery->where('name', "like", $this->search."%");
                });    
            });
        }

        

        if(isset($this->sort) && !empty($this->sort)){
            $this->pagelimit = 10;
            $this->page = 1;
            foreach ($this->sort as $sort) {
                $team->orderBy($sort['id'], ($sort['desc'] == true) ? 'DESC': 'ASC');
            }
        }
        $team = $team->paginate($this->pagelimit, ['*'], 'pageIndex', $this->page);

        return response()->json(['users' => $team], 200);
    }

    public function create()
    {
        try {
            return response()->json(['roles' => Role::get()->pluck('name'), 'status' => ['ACTIVE', 'INACTIVE']], 200);
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
            'contact' => 'required|digits:10',
        ]);

        DB::beginTransaction();
        try {

            $team = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'contact' => $request->contact,
            ]);

            $team->guard_name = 'sanctum';
            $team->assignRole($request->role);

            DB::commit();
            return response()->json(['user' => $request->all()], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(["message"=> "Error", "errors" => $e->getMessage()], 400);
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
            return response()->json(["message"=> "Error", "errors" => $th->getMessage()], 400);
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

            return response()->json(["user"=>$user, 'roles' => Role::get()->pluck('name'), 'status' => ['ACTIVE', 'INACTIVE']], 200);
        } catch (\Throwable $th) {
            return response()->json(["message"=> "Error", "errors" => $th->getMessage()], 400);
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
            'contact' => 'required|digits:10',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        DB::beginTransaction();
        try {

            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->contact = $request->contact;
            $user->status = $request->status;
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
