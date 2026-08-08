@extends('layouts.user_type.guest')

@section('title', 'Create an account — DigiStar Booking')

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
                Borrower access,<br>ready in <span>one minute</span>.
            </h2>
            <p class="ds-auth-copy">
                Register once and you can browse available equipment, raise a
                booking and follow its approval from the same dashboard.
            </p>

            <ul class="ds-auth-points">
                <li><i class="fas fa-check"></i> See what's free before you commit</li>
                <li><i class="fas fa-check"></i> Track every request you've filed</li>
                <li><i class="fas fa-check"></i> Print approvals straight from the record</li>
            </ul>
        </div>

        <p class="mb-0 text-xs" style="color: rgba(255,255,255,.45)">
            Cebu Graphicstar Imaging Corp. &middot; Your No.&nbsp;1 LED signage provider
        </p>
    </aside>

    {{-- Registration panel --}}
    <main class="ds-auth-main">
        <div class="ds-auth-form">

            <div class="ds-auth-mobilebrand">
                <span class="ds-rail-mark">
                    <img src="{{ asset('assets/img/sidebar-logo.png') }}" alt="DigiStar">
                </span>
                DigiStar Booking
            </div>

            <p class="ds-eyebrow mb-2">Borrower registration</p>
            <h1>Create your account</h1>
            <p class="lead">Fill in your details — a unit admin activates your access.</p>

            <form method="POST" action="/register" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="name">Full name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="form-control" placeholder="Juan dela Cruz" autocomplete="name">
                    @error('name')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email">Work email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="form-control" placeholder="you@graphicstar.com.ph" autocomplete="email">
                    @error('email')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone">Contact number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="form-control" placeholder="09xx xxx xxxx" autocomplete="tel">
                    @error('phone')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control" placeholder="••••••••" autocomplete="new-password">
                    @error('password')
                        <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="agreement" id="agreement" checked>
                    <label class="form-check-label mb-0" for="agreement">
                        I accept the
                        <a href="javascript:;" class="font-weight-bold" data-bs-toggle="modal"
                            data-bs-target="#terms-and-conditions">terms of use</a>
                    </label>
                    @error('agreement')
                        <p class="text-danger text-xs mt-2 mb-0">
                            You need to accept the terms of use before registering.
                        </p>
                    @enderror
                </div>

                <button type="submit" class="btn bg-gradient-info btn-lg w-100 mb-0">
                    Create account
                    <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </form>

            <p class="ds-auth-alt mb-0">
                Already registered? <a href="{{ url('login') }}">Sign in instead</a>
            </p>

        </div>
    </main>

</div>

{{-- Terms of use --}}
<div class="modal fade" id="terms-and-conditions" tabindex="-1" aria-labelledby="terms-and-conditions-title"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="terms-and-conditions-title">DigiStar Booking — Terms of Use</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p class="text-sm text-muted">
                    These terms govern how equipment is reserved, used and returned through DigiStar Booking.
                </p>

                <h6 class="mt-4">Accounts</h6>
                <ol class="text-sm">
                    <li>Register with accurate details — full name, work email, contact number and any other profile
                        information the system asks for.</li>
                    <li>Only registered accounts may raise or approve equipment bookings.</li>
                    <li>Keep your password to yourself; accounts are personal and must not be shared.</li>
                    <li>Anything done through your account is treated as done by you.</li>
                </ol>

                <h6 class="mt-4">Reserving equipment</h6>
                <ol class="text-sm">
                    <li>Any item listed as available may be requested, subject to the rules of the owning unit.</li>
                    <li>Every request must state a start date and an end date.</li>
                    <li>A unit admin approves or declines each request based on availability and unit priorities.</li>
                    <li>Equipment is issued for company work only, not for personal or commercial side use.</li>
                </ol>

                <h6 class="mt-4">While the item is with you</h6>
                <ol class="text-sm">
                    <li>Use it only within the approved booking window.</li>
                    <li>Keep it in working condition for the whole loan period.</li>
                    <li>Report damage or loss to the unit admin as soon as it happens.</li>
                    <li>Do not modify, dismantle or relocate an item without the unit admin's approval.</li>
                </ol>

                <h6 class="mt-4">Cancelling</h6>
                <ol class="text-sm">
                    <li>A request may be cancelled at any point before it is approved.</li>
                    <li>Cancellations go through the system, not by message or phone.</li>
                    <li>Cancelling after approval may still carry the charges or penalties set by the unit.</li>
                </ol>

                <h6 class="mt-4">Suspension of access</h6>
                <ol class="text-sm">
                    <li>Unit admins may suspend an account that breaches these terms.</li>
                    <li>A suspension may be temporary or permanent, at the unit admin's discretion.</li>
                    <li>Suspended accounts cannot raise bookings until access is restored.</li>
                </ol>

                <p class="text-sm text-muted mt-4 mb-0">
                    These terms may change as company policy changes; you will be notified by email or in-app notice.
                    Continuing to use DigiStar Booking means you accept the terms in force at the time.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-primary mb-0" data-bs-dismiss="modal">
                    I've read and understood
                </button>
            </div>

        </div>
    </div>
</div>

@endsection
