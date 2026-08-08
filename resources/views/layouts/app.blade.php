<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a2647">

    <title>@yield('title', 'DigiStar Booking')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Typefaces: Sora for headings, Plus Jakarta Sans for everything else --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700&display=swap"
        rel="stylesheet">

    {{-- Icons --}}
    <script src="https://kit.fontawesome.com/3343256dc4.js" crossorigin="anonymous"></script>

    {{-- Base component styles, then the DigiStar theme layer that overrides them --}}
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.3') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/digistar-theme.css?v=2.0.0') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css?v=2.0.0') }}" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>

<body class="g-sidenav-show">
    @auth
        @yield('auth')
    @endauth

    @guest
        @yield('guest')
    @endguest

    {{-- Core scripts --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/perfect-scrollbar/1.5.5/perfect-scrollbar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/smooth-scrollbar/8.8.4/smooth-scrollbar.js"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.js?v=1.0.3') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        // Windows scrollbars are chunky enough to shift the rail layout, so the
        // sidebar gets a virtual one on that platform only.
        if (navigator.platform.indexOf('Win') > -1 && document.querySelector('#sidenav-scrollbar')) {
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
        }

        // The rail toggle (#iconNavbarSidenav / #iconSidenav) is wired up by
        // soft-ui-dashboard.js — binding it again here would toggle twice and
        // leave the rail shut on phones.

        // Flash messages clear themselves after a few seconds.
        window.setTimeout(function () {
            document.querySelectorAll('[data-autodismiss]').forEach(function (el) {
                el.style.transition = 'opacity .4s ease';
                el.style.opacity = '0';
                window.setTimeout(function () { el.remove(); }, 400);
            });
        }, 4000);
    </script>

    @stack('scripts')
</body>

</html>
