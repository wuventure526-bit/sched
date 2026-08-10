@extends('layouts.user_type.auth')

@section('title', 'Edit user — DigiStar Booking')

@section('content')
@include('components.notifications')

<div class="card mx-3 mb-3">
  <div class="card-header pb-3">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h6 class="m-0">Edit {{ $user->name }}</h6>
        <p class="text-sm mb-0">Change the account's details, role or password.</p>
      </div>
      @if($user->trashed())
        <span class="badge bg-gradient-danger">Inactive — cannot sign in</span>
      @endif
    </div>
  </div>
  <div class="card-body pt-0">
    <form method="POST" action="{{ route('users.update', $user->id) }}">
      @csrf
      @method('PUT')

      @include('administrator.users._form')

      <div class="row mt-3">
        <div class="col-md-12">
          <button type="submit" class="btn bg-gradient-primary me-2">Save changes</button>
          <a href="{{ route('users.index') }}" class="btn bg-gradient-info">Cancel</a>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
