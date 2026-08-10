@extends('layouts.user_type.auth')

@section('title', 'Users — DigiStar Booking')

@section('content')
@include('components.notifications')

<div class="card mx-3 mb-4">
  <div class="card-header pb-0">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h6 class="m-0">User accounts</h6>
        <p class="text-sm">Create, edit and deactivate administrators, unit admins and borrowers.</p>
      </div>
      <div>
        <h6 class="m-0 text-sm">Total number of:</h6>
        <p class="d-inline-block me-2 text-sm">Administrators: {{ $counts['administrator'] }}</p>
        <p class="d-inline-block me-2 text-sm">Unit Admins: {{ $counts['unitadmin'] }}</p>
        <p class="d-inline-block me-2 text-sm">Borrowers: {{ $counts['borrower'] }}</p>
        <p class="d-inline-block text-sm">Inactive: {{ $counts['inactive'] }}</p>
      </div>
      <div class="form-group mb-3">
        <form action="{{ route('users.index') }}" method="GET">
          {{-- Preserve the active filters while searching --}}
          <input type="hidden" name="role" value="{{ $role }}">
          <input type="hidden" name="status" value="{{ $status }}">
          <div class="input-group">
            <button class="input-group-text search-icon" type="submit"><i class="fas fa-search"></i></button>
            <input class="form-control px-2" name="search" placeholder="Search" type="text" value="{{ request('search') }}">
          </div>
        </form>
      </div>
      <div class="ml-auto p-0">
        <a href="{{ route('users.create') }}" class="btn bg-gradient-primary">
          <i class="fas fa-user-plus me-1"></i> Add User
        </a>
      </div>
    </div>
  </div>

  <div class="card-body px-0 pt-0 pb-2">
    {{-- Role filter --}}
    <div class="btn-group mb-2">
      <a class="px-4 py-2 mb-0 btn btn-white text-normal {{ empty($role) ? 'tab-active' : '' }}"
         href="{{ route('users.index', ['status' => $status]) }}">All Roles</a>
      @foreach ($roles as $value => $label)
        <a class="px-4 py-2 mb-0 btn btn-white text-normal {{ $role === $value ? 'tab-active' : '' }}"
           href="{{ route('users.index', ['role' => $value, 'status' => $status]) }}">{{ $label }}</a>
      @endforeach
    </div>

    {{-- Status filter --}}
    <div class="btn-group mb-2 ms-md-3">
      <a class="px-4 py-2 mb-0 btn btn-white text-normal {{ empty($status) ? 'tab-active' : '' }}"
         href="{{ route('users.index', ['role' => $role]) }}">All ({{ $counts['total'] }})</a>
      <a class="px-4 py-2 mb-0 btn btn-white text-normal {{ $status === 'active' ? 'tab-active' : '' }}"
         href="{{ route('users.index', ['role' => $role, 'status' => 'active']) }}">Active ({{ $counts['active'] }})</a>
      <a class="px-4 py-2 mb-0 btn btn-white text-normal {{ $status === 'inactive' ? 'tab-active' : '' }}"
         href="{{ route('users.index', ['role' => $role, 'status' => 'inactive']) }}">Inactive ({{ $counts['inactive'] }})</a>
    </div>

    <div class="table-responsive p-0">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th class="text-secondary text-xxs font-weight-bolder pe-3">ID</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">User</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">Role</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">Unit</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">Phone</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">Status</th>
            <th class="text-secondary text-xxs font-weight-bolder px-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $account)
          <tr @if($account->trashed()) class="opacity-6" @endif>
            <td>
              <p class="text-xs font-weight-bold mb-0 ps-3">{{ $account->id }}</p>
            </td>
            <td>
              <div class="d-flex align-items-center">
                <img src="{{ asset($account->photo) }}" class="avatar avatar-sm me-3" alt="{{ $account->name }}">
                <div class="d-flex flex-column">
                  <h6 class="mb-0 text-sm">
                    {{ $account->name }}
                    @if($account->id === auth()->id())
                      <span class="badge bg-gradient-primary ms-1">You</span>
                    @endif
                  </h6>
                  <p class="text-xs text-secondary mb-0">{{ $account->email }}</p>
                </div>
              </div>
            </td>
            <td>
              <span class="badge {{ $account->role === 'administrator' ? 'bg-gradient-info' : 'bg-gradient-secondary' }}">
                {{ $roles[$account->role] ?? $account->role }}
              </span>
            </td>
            <td>
              <p class="text-xs font-weight-bold mb-0">{{ $account->unit->name ?? '—' }}</p>
            </td>
            <td>
              <p class="text-xs font-weight-bold mb-0">{{ $account->phone ?: '—' }}</p>
            </td>
            <td>
              @if($account->trashed())
                <span class="badge bg-gradient-danger">Inactive</span>
              @else
                <span class="badge bg-gradient-success">Active</span>
              @endif
            </td>
            <td>
              <div class="d-flex align-items-center">
                <a href="{{ route('users.show', $account->id) }}" class="me-2">
                  <button type="button" class="btn btn-action btn-info mb-0" title="View this account">
                    <i class="fas fa-eye"></i>
                  </button>
                </a>
                <a href="{{ route('users.edit', $account->id) }}">
                  <button type="button" class="btn btn-action btn-primary mb-0 me-1" title="Edit this account">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                </a>

                @if($account->trashed())
                  <form action="{{ route('users.reactivate', $account->id) }}" method="POST"
                        onsubmit="return confirm('Reactivate {{ $account->name }}? They will be able to sign in again.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-action mb-0 ms-1 btn-success" title="Reactivate this account">
                      <i class="fas fa-rotate-left"></i>
                    </button>
                  </form>
                @elseif($account->id !== auth()->id())
                  <form action="{{ route('users.deactivate', $account->id) }}" method="POST"
                        onsubmit="return confirm('Deactivate {{ $account->name }}? They will not be able to sign in. Their bookings and history are kept, and you can reactivate them later.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-action mb-0 ms-1 btn-danger" title="Deactivate this account">
                      <i class="fas fa-user-slash"></i>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-sm text-secondary py-4">
              No accounts match this filter.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@if($users->hasPages())
<div class="mb-4">
  <ul class="pagination pagination-info justify-content-center">
    <li class="page-item {{ $users->onFirstPage() ? 'disabled' : '' }}">
      <a class="page-link" href="{{ $users->previousPageUrl() }}" aria-label="Previous">
        <span aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
      </a>
    </li>

    @for ($i = 1; $i <= $users->lastPage(); $i++)
      <li class="page-item{{ $users->currentPage() == $i ? ' active' : '' }}">
        <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
      </li>
    @endfor

    <li class="page-item {{ $users->hasMorePages() ? '' : 'disabled' }}">
      <a class="page-link" href="{{ $users->nextPageUrl() }}" aria-label="Next">
        <span aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
      </a>
    </li>
  </ul>
</div>
@endif

@endsection
