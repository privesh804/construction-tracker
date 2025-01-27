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

            $postArr = $request->all();

            $admin = User::where([
                'email' => $postArr['email'],
            ])->firstOrFail();

            if(Hash::check($postArr['password'], $admin->password)){
                $token = $admin->createToken('Super-Admin');
                return response()->json(['token' => $token->plainTextToken]);
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
