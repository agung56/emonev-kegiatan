<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'login'    => 'required|string', // Ini bisa berisi NIP atau Email
            'password' => 'required|string',
        ]);

        // 2. Tentukan apakah input adalah email atau nip
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        // 3. Coba Autentikasi
        if (Auth::attempt([$loginType => $request->login, 'password' => $request->password], $request->remember)) {
            $request->session()->regenerate();

            if (Auth::attempt([$loginType => $request->login, 'password' => $request->password], $request->remember)) {
                $request->session()->regenerate();
                
                // Semua role diarahkan ke dashboard utama
                return redirect()->intended('/dashboard');
            }
        }

        // Jika gagal
        return back()->withErrors([
            'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}