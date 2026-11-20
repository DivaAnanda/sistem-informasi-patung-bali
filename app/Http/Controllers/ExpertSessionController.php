<?php

namespace App\Http\Controllers;

use App\Support\Expert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpertSessionController extends Controller
{
    public function store(Request $request, string $token): RedirectResponse
    {
        abort_unless(Expert::tokenMatches(config('patung.expert_token'), $token), 404);

        $request->session()->regenerate();
        $request->session()->put(Expert::SESSION_KEY, Expert::fingerprint($token));

        return redirect()->route('reviews.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(Expert::SESSION_KEY);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
