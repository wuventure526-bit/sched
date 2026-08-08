@extends('layouts.app')

@section('guest')

    {{-- Sign in and sign up own the whole viewport (split-screen layout), so no
         shared navigation is injected around them. --}}
    @if (Request::is('login') || Request::is('register'))

        @yield('content')

    @else

        @include('layouts.navbars.guest.nav')

        <main class="container py-5">
            @yield('content')
        </main>

        @include('layouts.footers.guest.footer')

    @endif

@endsection
