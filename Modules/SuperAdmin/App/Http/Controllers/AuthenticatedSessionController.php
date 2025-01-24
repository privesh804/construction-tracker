<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AuthenticatedSessionController extends Controller
{
    function login(Request $request){
        $request->validate([
            'email' => 'required|email:rfc,dns,spoof',
            'password' => 'required',
        ]);

        try {

            $postArr = $request->all();

            $admin = User::where([
                'email' => $postArr['email'],
            ])->firstOrFail();

            if(Hash::check($postArr['pasword'], $admin->password)){
                $token = $admin->createToken($request->token_name);
                return response()->json(['token' => $token->plainTextToken]);
            } else {
                throw new \Exception("The provided credentials do not match our records.", 401);
            }    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
