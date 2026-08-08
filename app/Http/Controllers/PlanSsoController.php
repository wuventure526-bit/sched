<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanSsoController extends Controller
{
    public function redirectToPlan(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'User not authenticated');
        }

        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        DB::connection('app2')->table('sso_tokens')->insert([
            'email' => $user->email,
            'token' => $hashedToken,
            'expires_at' => now()->addMinutes(60),
            'used' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->away('http://192.168.0.105:8000/sso/login?token=' . urlencode($plainToken));
    }
}