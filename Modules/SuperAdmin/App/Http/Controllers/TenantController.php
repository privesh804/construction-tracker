<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use Str;
Use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class TenantController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|min:2|max:100',
            'domain' => ['required'],
        ]);

        try {

            $tenant = Tenant::create([
                'name' => $request->name,
            ]);

            $tenant->domains()->create([
                'domain' => Str::lower(str_replace(' ', '', $request->domain)).'.'.config('app.domain'),
            ]); 
            return response()->json(['organisation' => $tenant], 201);
        } catch (\Exception $e) {
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    public function delete($id)
    {
        try {
            $tenant = Tenant::with('domains')->find($id);
            $tenant->delete();
            return response()->json([], 204);
        } catch(\Exception $e){
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }
}
