<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        return view('session.login-session');
    }

 public function store(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $remember = $request->has('rememberMe');

    if (Auth::attempt($credentials, $remember)) {
        $request->session()->regenerate();

        $user = Auth::user();

        // Role-based redirection
        if ($user->role === 'administrator') {
            return redirect()->route('dashboard.index')->with('success', 'Good morning Graphicstarian!');
        } elseif ($user->role === 'unitadmin') {
            return redirect()->route('usages.index')->with('success', 'Good morning Graphicstarian!');
        } elseif ($user->role === 'borrower') {
            return redirect()->route('bookings.index')->with('success', 'Welcome Good morning Graphicstarian!');
        } else {
            return redirect()->route('dashboard.index')->with('success', 'Good morning Graphicstarian!');
        }
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
}




    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with(['success' => 'You have logged out of the system.']);
    }
}
