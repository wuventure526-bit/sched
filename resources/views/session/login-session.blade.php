@extends('layouts.user_type.guest')

@section('title', 'Sign in — DigiStar Booking')

@section('content')

<div class="ds-auth">

    {{-- Brand panel (large screens only) --}}
    <aside class="ds-auth-aside">
        <div class="ds-auth-brand">
            <span class="ds-rail-mark">
                <img src="{{ asset('assets/img/sidebar-logo.png') }}" alt="DigiStar">
            </span>
            DigiStar
        </div>

        <div>
            <h2 class="ds-auth-headline">
                Reserve equipment<br>without the <span>paperwork</span>.
            </h2>
            <p class="ds-auth-copy">
                One workspace for requests, approvals and returns across every
                Graphicstar unit — so nobody has to chase a form again.
            </p>

            <ul class="ds-auth-points">
                <li><i class="fas fa-check"></i> Live availability before you book</li>
                <li><i class="fas fa-check"></i> Approvals routed to the right unit admin</li>
                <li><i class="fas fa-check"></i> Usage and returns tracked end to end</li>
            </ul>
        </div>

        <p class="mb-0 text-xs" style="color: rgba(255,255,255,.45)">
            Cebu Graphicstar Imaging Corp. &middot; Your No.&nbsp;1 LED signage provider
        </p>
    </aside>

    {{-- Credentials panel --}}
    <main class="ds-auth-main">
        <div class="ds-auth-form">

            <div class="ds-auth-mobilebrand">
                <span class="ds-rail-mark">
                    <img src="{{ asset('assets/img/sidebar-logo.png') }}" alt="DigiStar">
                </span>
                DigiStar Booking
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-4" data-autodismiss>{{ session('success') }}</div>
            @endif

            <p class="ds-eyebrow mb-2">Welcome back</p>
            <h1>Sign in</h1>
            <p class="lead">Use the account issued to you by your unit administrator.</p>

            <form method="POST" action="/login" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email">Work email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="you@graphicstar.com.ph" autocomplete="email" autofocus>
                    @error('email')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••" autocomplete="current-password">
                    @error('password')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" name="rememberMe" type="checkbox" id="rememberMe" checked>
                    <label class="form-check-label mb-0" for="rememberMe">Keep me signed in</label>
                </div>

                <button type="submit" class="btn bg-gradient-primary btn-lg w-100 mb-0">
                    Sign in
                    <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </form>

            <p class="ds-auth-alt mb-0">
                No account yet? <a href="{{ url('register') }}">Register as a borrower</a>
            </p>

        </div>
    </main>

</div>

@endsection
