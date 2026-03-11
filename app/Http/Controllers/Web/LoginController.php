<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Votre compte est désactivé.']);
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'supplier' => redirect()->route('supplier.dashboard'),
            'seller'   => redirect()->route('seller.dashboard'),
            'client'   => redirect()->route('client.dashboard'),
            default    => redirect('/'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
