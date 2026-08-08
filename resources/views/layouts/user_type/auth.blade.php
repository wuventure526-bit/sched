@extends('layouts.app')

@section('auth')

    {{-- The two static demo screens keep the marketing chrome; everything else
         runs inside the application shell (rail + top bar + work area). --}}
    @if (Request::is('static-sign-up') || Request::is('static-sign-in'))

        @include('layouts.navbars.guest.nav')
        @yield('content')
        @include('layouts.footers.guest.footer')

    @else

        @include('layouts.navbars.auth.sidebar')

        <main class="main-content position-relative">
            @include('layouts.navbars.auth.nav')

            <div class="container-fluid px-4 py-4">
                @yield('content')
                @include('layouts.footers.auth.footer')
            </div>
        </main>

    @endif

@endsection
