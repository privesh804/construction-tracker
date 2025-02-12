<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Modules\SuperAdmin\App\Models\{User, Tenant, Domain};


class AuthenticatedSessionController extends Controller
{

    function checkEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email:rfc,dns'
            ]);

            $main = User::where([
                'email' => $request->email,
            ])->first();

            $return = false;

            if(isset($main) && !empty($main)){
                $return = true;
                return response()->json(['status' => $return]);
            } else {
                $tenants = Tenant::all();
    
                foreach ($tenants as $tenant) {
                    $domain = Domain::where('tenant_id', $tenant->id)->first();
                }

                if(isset($domain) && !empty($domain)){
                    foreach ($tenants as $tenant) {
    
                        tenancy()->initialize($tenant);
    
                        $tenantConnection = 'tenant' . $tenant->id;
                        $user = \DB::table("users")->where('email', $request->email)->first();
                        
                        if (isset($user) && !empty($user)) {
                            $return = $domain->domain."/login?email=".$request->email;
                            break;
                        }
                    }
                }

            }


            if($return == false){
                throw new \Exception("Invalid Email.", 401);
            } else {
                return response()->json(['url' => $return]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    function login(Request $request){
        
        try {
            $request->validate([
                'email' => 'required|email:rfc,dns',
                'password' => 'required',
            ]);


            $admin = User::where([
                'email' => $request->email,
            ])->firstOrFail();

            if(Hash::check($request->password, $admin->password)){

                // $admin->tokens()->delete();

                $token = $admin->createToken('Super-Admin', ['*'], now()->addDay())->plainTextToken;
                return response()->json(['token' => $token]);
            } else {
                throw new \Exception("The provided credentials do not match our records.", 401);
            }    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
