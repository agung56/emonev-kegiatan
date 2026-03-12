<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string', // Ini bisa berisi NIP atau Email
            'password' => 'required|string',
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'login' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . $seconds . ' detik.',
            ])->onlyInput('login');
        }

        $user = User::where($loginType, $request->login)->first();

        if (! $user) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ])->onlyInput('login');
        }

        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'User nonaktif. Hubungi administrator.',
            ])->onlyInput('login');
        }

        if (! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        RateLimiter::clear($throttleKey);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->string('login')->toString()) . '|' . $request->ip();
    }
}
