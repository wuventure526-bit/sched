@extends('layouts.user_type.auth')

@section('content')
<div class="card mx-3 mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="m-0">My Requests</h6>
    <a href="{{ route('requests.create') }}" class="btn btn-primary btn-sm">Create Request</a>
  </div>

  <div class="card-body">

    {{-- ? STATUS TABS --}}
    @php
      $currentStatus = request('status', 'all'); // all | submitted | noted | approved
      $tabs = [
        'all'       => 'All',
        'submitted' => 'Submitted',
        'noted'     => 'Noted',
        'approved'  => 'Approved',
      ];

      
    @endphp

    <ul class="nav nav-tabs mb-3">
      @foreach($tabs as $key => $label)
        <li class="nav-item">
          <a
            class="nav-link {{ $currentStatus === $key ? 'active' : '' }}"
            href="{{ $key === 'all' ? route('requests.index') : route('requests.index', ['status' => $key]) }}"
          >
            {{ $label }}
          </a>
        </li>
      @endforeach
    </ul>

    <div class="table-responsive">
      <table class="table table-striped align-items-center mb-0">
        <thead>
          <tr>
            <th>Request No</th>
            <th>Type</th>
            <th>Status</th>
            <th>Created</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($docs as $d)
            <tr>
              <td>{{ $d->request_no }}</td>
              <td>{{ strtoupper($d->type) }}</td>
              <td>{{ strtoupper($d->status) }}</td>
              <td>{{ optional($d->created_at)->format('Y-m-d') }}</td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('requests.show', $d->id) }}">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('requests.print', $d->id) }}">Print</a>
                <a class="btn btn-sm btn-outline-danger" href="{{ route('requests.pdf', $d->id) }}">PDF</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                No requests found for this status.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{-- ? keep the selected tab on pagination --}}
      {{ $docs->appends(request()->query())->links() }}
    </div>

  </div>
</div>
@endsection