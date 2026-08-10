@extends('layouts.user_type.auth')

@section('title', 'User detail — DigiStar Booking')

@section('content')
@include('components.notifications')

<div class="card mx-3 mb-3">
  <div class="card-header pb-3">
    <div class="d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <img src="{{ asset($user->photo) }}" class="avatar avatar-lg me-3" alt="{{ $user->name }}">
        <div>
          <h6 class="m-0">{{ $user->name }}</h6>
          <p class="text-sm mb-0">{{ $user->email }}</p>
        </div>
      </div>
      <div>
        <span class="badge {{ $user->role === 'administrator' ? 'bg-gradient-info' : 'bg-gradient-secondary' }}">
          {{ $roles[$user->role] ?? $user->role }}
        </span>
        @if($user->trashed())
          <span class="badge bg-gradient-danger ms-1">Inactive</span>
        @else
          <span class="badge bg-gradient-success ms-1">Active</span>
        @endif
      </div>
    </div>
  </div>

  <div class="card-body pt-0">
    <ul class="list-group">
      <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Unit:</strong> &nbsp; {{ $user->unit->name ?? '—' }}</li>
      <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Phone:</strong> &nbsp; {{ $user->phone ?: '—' }}</li>
      <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Address:</strong> &nbsp; {{ $user->address ?: '—' }}</li>
      <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">City:</strong> &nbsp; {{ $user->city ?: '—' }}</li>
      <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Created:</strong> &nbsp; {{ $user->created_at?->format('d M Y') ?? '—' }}</li>
      @if($user->trashed())
        <li class="list-group-item border-0 ps-0 text-sm">
          <strong class="text-dark">Deactivated:</strong> &nbsp; {{ $user->deleted_at?->format('d M Y') ?? '—' }}
        </li>
      @endif
    </ul>

    <div class="mt-4">
      <a href="{{ route('users.edit', $user->id) }}" class="btn bg-gradient-primary me-2">Edit account</a>

      @if($user->trashed())
        <form action="{{ route('users.reactivate', $user->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Reactivate {{ $user->name }}? They will be able to sign in again.');">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn bg-gradient-success me-2">Reactivate</button>
        </form>
      @elseif($user->id !== auth()->id())
        <form action="{{ route('users.deactivate', $user->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Deactivate {{ $user->name }}? They will not be able to sign in. Their history is kept.');">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn bg-gradient-danger me-2">Deactivate</button>
        </form>
      @endif

      <a href="{{ route('users.index') }}" class="btn bg-gradient-info">Back</a>
    </div>
  </div>
</div>

@endsection
