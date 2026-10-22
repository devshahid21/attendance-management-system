<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);
        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();

        return redirect()->route('account.edit')->with('message', 'Your password has been updated.');
    }
}
