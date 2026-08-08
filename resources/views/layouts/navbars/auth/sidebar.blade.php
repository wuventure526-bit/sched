@php
    /**
     * Navigation rail.
     *
     * Every entry is declared once here and rendered by a single loop below, so
     * adding a destination is a one-line change instead of another block of
     * copy-pasted markup. "match" holds the URL patterns that light the row up.
     */
    // Everyone gets these two, whatever their role — matching the previous rail,
    // where the dashboard and the request module sat outside the role blocks.
    $railLinks = [
        ['label' => 'Overview', 'icon' => 'fa-gauge-high',          'url' => url('dashboard'),       'match' => ['dashboard']],
        ['label' => 'Requests', 'icon' => 'fa-file-invoice-dollar', 'url' => route('requests.index'), 'match' => ['requests', 'requests/*']],
    ];

    if (Gate::allows('administrator')) {
        $railRole = 'Administrator';
        $railLinks = array_merge($railLinks, [
            ['label' => 'Categories',  'icon' => 'fa-layer-group',   'url' => url('categories'), 'match' => ['categories', 'categories/*']],
            ['label' => 'Unit Admins', 'icon' => 'fa-user-shield',   'url' => url('unitadmins'), 'match' => ['unitadmins', 'unitadmins/*']],
            ['label' => 'Units',       'icon' => 'fa-sitemap',       'url' => url('units'),      'match' => ['units', 'units/*']],
            ['label' => 'Bookings',    'icon' => 'fa-calendar-days',   'url' => url('bookings'),      'match' => ['bookings', 'bookings/*']],
            ['label' => 'Plan Board',  'icon' => 'fa-diagram-project', 'url' => route('plan.launch'), 'match' => ['plan', 'plan/*']],
            ['label' => 'Inventory',   'icon' => 'fa-boxes-stacked',   'url' => url('items'),         'match' => ['items', 'items/*']],
            ['label' => 'Usage Log',   'icon' => 'fa-chart-column',    'url' => url('usages'),        'match' => ['usages', 'usages/*']],
        ]);
    } elseif (Gate::allows('unitadmin')) {
        $railRole = 'Unit Admin';
        $railLinks = array_merge($railLinks, [
            ['label' => 'Bookings',   'icon' => 'fa-calendar-days',   'url' => url('bookings'),      'match' => ['bookings', 'bookings/*']],
            ['label' => 'Plan Board', 'icon' => 'fa-diagram-project', 'url' => route('plan.launch'), 'match' => ['plan', 'plan/*']],
            ['label' => 'Inventory',  'icon' => 'fa-boxes-stacked',   'url' => url('items'),         'match' => ['items', 'items/*']],
            ['label' => 'Usage Log',  'icon' => 'fa-chart-column',    'url' => url('usages'),        'match' => ['usages', 'usages/*']],
        ]);
    } elseif (Gate::allows('borrower')) {
        $railRole = 'Borrower';
        $railLinks = array_merge($railLinks, [
            ['label' => 'My Bookings',  'icon' => 'fa-calendar-check',  'url' => url('bookings'),      'match' => ['bookings', 'bookings/*']],
            ['label' => 'Browse Items', 'icon' => 'fa-boxes-stacked',   'url' => url('items'),         'match' => ['items', 'items/*']],
            ['label' => 'Plan Board',   'icon' => 'fa-diagram-project', 'url' => route('plan.launch'), 'match' => ['plan', 'plan/*']],
        ]);
    } else {
        // No role granted yet — the request module is all that is reachable.
        $railRole = 'Workspace';
    }

    $railLinks[] = ['label' => 'My Account', 'icon' => 'fa-circle-user', 'url' => url('profile'), 'match' => ['profile', 'profile/*']];
@endphp

<aside class="sidenav" id="sidenav-main">

    <div class="ds-rail-head">
        <a href="{{ route('dashboard.index') }}" class="ds-rail-mark">
            <img src="{{ asset('assets/img/sidebar-logo.png') }}" alt="DigiStar">
        </a>
        <a href="{{ route('dashboard.index') }}" class="text-decoration-none">
            <span class="ds-rail-name">DigiStar</span>
            <span class="ds-rail-sub">Booking Suite</span>
        </a>
        <i class="fas fa-xmark ms-auto text-white opacity-6 cursor-pointer d-xl-none" id="iconSidenav"
            aria-hidden="true"></i>
    </div>

    <nav class="ds-rail-nav" id="sidenav-collapse-main" aria-label="Main navigation">
        <div class="ds-rail-group">{{ $railRole }}</div>

        <ul>
            @foreach ($railLinks as $link)
                @php $isActive = Request::is($link['match']); @endphp
                <li>
                    <a class="ds-rail-link {{ $isActive ? 'active' : '' }}" href="{{ $link['url'] }}"
                        @if ($isActive) aria-current="page" @endif>
                        <i class="fas {{ $link['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="ds-rail-foot">
        <h6>Raw material lookup</h6>
        <p>Stock levels and material records live in the GSUITE inventory console.</p>
        <a href="http://gsuite.graphicstar.com.ph/#/inventories" target="_blank" rel="noopener"
            class="btn bg-gradient-info btn-sm w-100 mb-0">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> Open GSUITE
        </a>
    </div>

</aside>
