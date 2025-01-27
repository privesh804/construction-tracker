<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
Use App\Models\Tenant;
use Str, DB;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|min:5|max:100',
                'domain' => ['required', Rule::unique('domains')->where(function ($query) use ($request) {
                    return $query->where('domain', Str::lower(str_replace(' ', '', $request->domain)).'.'.config('app.domain'));
                })],
            ]);

            $tenant = Tenant::create([
                'name' => $request->name,
            ]);

            $tenant->domains()->create([
                'domain' => Str::lower(str_replace(' ', '', $request->domain)).'.'.config('app.domain'),
            ]); 
            DB::commit();
            return response()->json(['organisation' => $tenant], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function delete($id)
    {
        // dd($id);
        $tenant = Tenant::with('domains')->find($id);
        $tenant->delete();
        // tenant()->delete(tenant()->getTenantIdByDomain($id));
        return redirect()->route('dashboard');
    }
}
