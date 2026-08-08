<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegistrationCredentialsNotification;

class RegisterController extends Controller
{
    public function create()
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }

        return view('session.register');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'max:50', Rule::unique('users', 'phone')],
            'password' => ['required', 'min:5', 'max:20'],
            'agreement' => ['accepted'],
        ]);

        // ? Keep plain password for email (internal use)
        $plainPassword = $attributes['password'];

        // ? Hash password for DB
        $attributes['password'] = bcrypt($plainPassword);

        // ? Set role
        $attributes['role'] = 'borrower';

        // ? Create user
        $user = User::create($attributes);

        // ? Send email credentials
        $user->notify(new RegistrationCredentialsNotification($user->email, $plainPassword));

        return redirect('/login')->with(
            'success',
            'Account created! Login details have been emailed to your registered address.'
        );
    }
}
