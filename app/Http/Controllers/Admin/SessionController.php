<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class SessionController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 300;

    public function create(): View|RedirectResponse
    {
        abort_unless(Admin::enabled(), 404);

        return Admin::active() ? redirect()->route('admin.statues.index') : view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Admin::enabled(), 404);

        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'max:200'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $key = 'admin-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return back()->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit."])->onlyInput('email');
        }

        if (! Admin::attempt($credentials['email'], $credentials['password'])) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put(Admin::SESSION_KEY, Admin::fingerprint());

        return redirect()->intended(route('admin.statues.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(Admin::SESSION_KEY);
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
