@extends('layouts.user_type.auth')

@section('content')
<div class="card mx-3 mb-4">
  <div class="card-header">
    <h6 class="m-0">Requests for Approval</h6>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Request No</th>
            <th>Type</th>
            <th>Requester</th>
            <th>Status</th>
            <th>Submitted</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($docs as $d)
          <tr>
            <td>{{ $d->request_no }}</td>
            <td>{{ strtoupper($d->type) }}</td>
            <td>{{ optional($d->user)->name ?? 'User#'.$d->user_id }}</td>
            <td>{{ strtoupper($d->status) }}</td>
            <td>{{ optional($d->submitted_at)?->format('Y-m-d H:i') }}</td>
            <td class="text-end">

              <a class="btn btn-sm btn-outline-primary" href="{{ route('requests.show', $d->id) }}">View</a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('requests.print', $d->id) }}">Print</a>

              @if($d->status === 'submitted')
              <form class="d-inline" method="POST" action="{{ route('requests.note', $d->id) }}">
                @csrf
                <button class="btn btn-sm btn-warning" type="submit">Note</button>
              </form>
              @endif

              <form class="d-inline" method="POST" action="{{ route('requests.approve', $d->id) }}">
                @csrf
                <button class="btn btn-sm btn-success" type="submit">Approve</button>
              </form>

              <button class="btn btn-sm btn-danger" type="button" onclick="openReject({{ $d->id }})">Reject</button>

            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{ $docs->links() }}

    <form id="rejectForm" method="POST" style="display:none;">
      @csrf
      <input type="hidden" name="reason" id="rejectReason">
    </form>

  </div>
</div>

<script>
function openReject(id){
  const reason = prompt("Reason for rejection:");
  if(!reason) return;

  const form = document.getElementById('rejectForm');
  document.getElementById('rejectReason').value = reason;

  form.action = `/requests/${id}/reject`;
  form.submit();
}
</script>
@endsection