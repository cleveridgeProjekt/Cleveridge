<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show SPA entrypoint for login/register
    public function showLogin()   { return view('app'); }
    public function showRegister(){ return view('app'); }

    // Handle login POST
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Ungültige Login-Daten'], 401);
    }

    // Handle register POST
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'surname' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'username' => ['required', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'], // expects password_confirmation field
        ]);
        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);
        Auth::login($user);
        return response()->json(['success' => true]);
    }

    // Handle logout POST
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['success' => true]);
    }
}
