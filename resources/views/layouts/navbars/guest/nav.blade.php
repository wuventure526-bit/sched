@php $signedIn = auth()->check(); @endphp

<nav class="ds-guestnav">

    <a class="navbar-brand" href="{{ $signedIn ? url('dashboard') : url('login') }}">
        <span class="ds-rail-mark">
            <img src="{{ asset('assets/img/sidebar-logo.png') }}" alt="DigiStar">
        </span>
        DigiStar
    </a>

    <button class="navbar-toggler shadow-none border-0 d-lg-none" type="button" data-bs-toggle="collapse"
        data-bs-target="#guest-navigation" aria-controls="guest-navigation" aria-expanded="false"
        aria-label="Toggle navigation">
        <i class="fas fa-bars text-dark"></i>
    </button>

    <div class="collapse navbar-collapse flex-grow-0" id="guest-navigation">
        <ul class="navbar-nav align-items-lg-center gap-lg-1">
            @if ($signedIn)
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('dashboard') }}">
                        <i class="fas fa-gauge-high me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('profile') }}">
                        <i class="fas fa-circle-user me-1"></i> Profile
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('register') }}">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn bg-gradient-primary btn-sm mb-0" href="{{ url('login') }}">Sign in</a>
                </li>
            @endif
        </ul>
    </div>

</nav>
