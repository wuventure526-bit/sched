@php
    $segments = collect(request()->segments());
    $pageTitle = $segments->isEmpty() ? 'Overview' : str_replace(['-', '_'], ' ', $segments->first());
    $trail = $segments->map(fn ($segment) => str_replace(['-', '_'], ' ', $segment));
    $user = Auth::user();

    // The `role` column is what the authorisation gates read, so the badge and
    // the unit chip follow it rather than the (largely unpopulated) Spatie table.
    $roleLabel = [
        'administrator' => 'Administrator',
        'unitadmin' => 'Unit Admin',
        'borrower' => 'Borrower',
    ][$user->role] ?? 'Member';
@endphp

<header class="ds-topbar" id="navbarBlur" navbar-scroll="true">

    <div class="d-flex align-items-center gap-3">
        <button type="button" class="ds-burger d-xl-none" id="iconNavbarSidenav" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div>
            <nav aria-label="breadcrumb">
                <span class="ds-topbar-path">
                    DigiStar
                    @foreach ($trail as $crumb)
                        <span class="sep">/</span>{{ $crumb }}
                    @endforeach
                </span>
            </nav>
            <h1 class="ds-topbar-title">{{ $pageTitle }}</h1>
        </div>
    </div>

    <div class="ds-topbar-actions">

        @if ($user->role === 'unitadmin' && $user->unit)
            <span class="ds-chip d-none d-md-inline-flex">
                <i class="fas fa-sitemap"></i>
                {{ $user->unit->name }} &middot; #{{ $user->unit->id }}
            </span>
        @endif

        <a href="{{ route('profile.index') }}" class="ds-user">
            <img src="{{ asset($user->photo) }}" alt="{{ $user->name }}">
            <span>
                <span class="ds-user-name">{{ $user->name }}</span>
                <span class="ds-user-role">{{ $roleLabel }}</span>
            </span>
        </a>

        <form class="mb-0" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="ds-signout" title="Sign out">
                <i class="fas fa-right-from-bracket"></i>
                <span>Sign out</span>
            </button>
        </form>

    </div>

</header>
