@extends('layouts.user_type.auth')

@section('title', 'Add user — DigiStar Booking')

@section('content')
@include('components.notifications')

<div class="card mx-3 mb-3">
  <div class="card-header pb-3">
    <h6 class="m-0">Add user</h6>
    <p class="text-sm mb-0">Create an administrator, unit admin or borrower account.</p>
  </div>
  <div class="card-body pt-0">
    <form method="POST" action="{{ route('users.store') }}">
      @csrf

      @include('administrator.users._form', ['user' => null])

      <div class="row mt-3">
        <div class="col-md-12">
          <button type="submit" class="btn bg-gradient-primary me-2">Create account</button>
          <a href="{{ route('users.index') }}" class="btn bg-gradient-info">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
