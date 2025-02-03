<?php

namespace App\Http\Controllers\Application;

use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function login () {
        return view('app.auth.login');
    }

    public function register () {
        return view('app.auth.register');
    }

    public function dash () {
        $users = User::with('roles')->get();
        return view('app.dashboard', [
            'users' => $users,
        ]);
    }

    public function registerrequest (Request $request) {
        // dd($request->all());
        $validate = $request->validate([
            'name' => 'required|regex:/^[A-z\s]+$/',
            'email' => 'required|regex:/^[A-z0-9.$!%]+@[a-z]+.[A-z]{2,}$/|unique:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        

        return response()->json([],201);
        // return redirect()->route('loginpage');
    }

    public function logvalidate (Request $request) {
        try {
            $request->validate([
                'email' => 'required|email:rfc,dns',
                'password' => 'required',
            ]);


            $tenantAdmin = User::where([
                'email' => $request->email,
            ])->firstOrFail();

            if(Hash::check($request->password, $tenantAdmin->password)){

                $tenantAdmin->tokens()->delete();

                $token = $tenantAdmin->createToken('Tenant-Admin', ['*'], now()->addDay())->plainTextToken;
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

    public function profile (Request $request) {
        // dd('found');
        return view('app.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function logoutrequest () {
        Auth::logout();
        return redirect()->route('welcome');
    }
}
