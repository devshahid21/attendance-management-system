<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function form() { return view('auth.login'); }
    public function login(Request $request) {
        $data = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if (!Auth::attempt($data)) return back()->withErrors(['email'=>'Invalid credentials.'])->onlyInput('email');
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
    public function logout(Request $request) {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
