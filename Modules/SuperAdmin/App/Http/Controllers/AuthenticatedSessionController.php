<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Modules\SuperAdmin\App\Models\User;

class AuthenticatedSessionController extends Controller
{
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

                $admin->tokens()->delete();

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
