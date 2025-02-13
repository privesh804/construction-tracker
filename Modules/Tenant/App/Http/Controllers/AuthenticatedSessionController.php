<?php

namespace Modules\Tenant\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Modules\Tenant\App\Models\User;

class AuthenticatedSessionController extends Controller
{
    function login(Request $request){
        
        try {
            $request->validate([
                'email' => 'required|email:rfc,dns',
                'password' => 'required',
            ]);


            $tenantuser = User::where([
                'email' => $request->email,
            ])->firstOrFail();

            if(Hash::check($request->password, $tenantuser->password)){

                // $tenantuser->tokens()->delete();

                $token = $tenantuser->createToken('Tenant', ['*'], now()->addDay())->plainTextToken;
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

    function logout(Request $request){
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

