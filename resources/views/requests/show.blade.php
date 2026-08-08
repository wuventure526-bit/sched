@extends('layouts.user_type.auth')

@section('content')
@php
  $role = auth()->user()->role ?? null;
  $isOwner = ($doc->user_id === auth()->id());

  // Liquidation fallback values (avoid blank fields)
  $ld = $doc->liquidationDetail;
  $weekNo  = $doc->week_no ?? ($ld->week_no ?? null);
  $dateFrom = $doc->date_from ?? ($ld->date_from ?? null);
  $dateTo   = $doc->date_to ?? ($ld->date_to ?? null);

  // Business trip detail
  $bd = $doc->businessTripDetail ?? null;

  // ? NEW Edit rule: owner can edit as long as status is NOT noted or approved
  $canEdit = $isOwner && !in_array($doc->status, ['noted','approved'], true);
  $editLockReason = "Editable only while NOT NOTED / NOT APPROVED";

  // ? Reject modal availability
  $canAdminReject = ($role === 'administrator' && in_array($doc->status, ['submitted','noted'], true));

  // Has items
  $hasItems = in_array($doc->type, ['payment','liquidation','revolving_fund'], true);

  // ? Show item date for liquidation + revolving fund
  $showItemDate = in_array($doc->type, ['liquidation','revolving_fund'], true);

  // colspans
  $itemsColspan = ($showItemDate ? 3 : 2) + ($doc->status === 'rejected' ? 1 : 0);

  // ? NEW: Print/PDF rule
  // Business trip: allow print when NOTED (or APPROVED)
  // Others: allow print only when APPROVED
  $canPrint = ($doc->type === 'business_trip')
      ? in_array($doc->status, ['noted','approved'], true)
      : ($doc->status === 'approved');

  $printLockMsg = ($doc->type === 'business_trip')
      ? 'Available after noting'
      : 'Available after approval';

  $printInfoMsg = ($doc->type === 'business_trip')
      ? 'Printing is locked until the request is <strong>NOTED</strong>.'
      : 'Printing is locked until the request is <strong>APPROVED</strong>.';

  $signatureMsg = ($doc->type === 'business_trip')
      ? 'Once noted, the GM e-signature will appear automatically on the print/PDF.'
      : 'Once approved, the GM e-signature will appear automatically on the print/PDF.';
@endphp

<div class="card mx-3 mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0">{{ $doc->request_no }} - {{ strtoupper($doc->type) }}</h6>
      <small>
        Status:
        <strong class="
          @if($doc->status === 'approved') text-success
          @elseif($doc->status === 'rejected') text-danger
          @elseif($doc->status === 'submitted' || $doc->status === 'noted') text-warning
          @else text-secondary
          @endif
        ">
          {{ strtoupper($doc->status) }}
        </strong>
      </small>

      <div class="mt-1">
        @if($doc->submitted_at)
          <small class="d-block">Submitted: {{ $doc->submitted_at->format('M d, Y h:i A') }}</small>
        @endif
        @if($doc->noted_at)
          <small class="d-block">Noted: {{ $doc->noted_at->format('M d, Y h:i A') }}</small>
        @endif
        @if($doc->approved_at)
          <small class="d-block">Approved: {{ $doc->approved_at->format('M d, Y h:i A') }}</small>
        @endif
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap justify-content-end">

      {{-- ? EDIT only if owner + NOT noted/approved --}}
      @if($canEdit)
        <a class="btn btn-sm btn-outline-warning" href="{{ route('requests.edit', $doc->id) }}">
          Edit
        </a>
      @else
        <button class="btn btn-sm btn-outline-warning" type="button" disabled title="{{ $editLockReason }}">
          Edit
        </button>
      @endif

      {{-- ? PRINT/PDF: Business Trip = NOTED/APPROVED, Others = APPROVED --}}
      @if($canPrint)
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('requests.print', $doc->id) }}">Print</a>
        <a class="btn btn-sm btn-outline-danger" href="{{ route('requests.pdf', $doc->id) }}">PDF</a>
      @else
        <button class="btn btn-sm btn-outline-secondary" type="button" disabled title="{{ $printLockMsg }}">
          Print
        </button>
        <button class="btn btn-sm btn-outline-danger" type="button" disabled title="{{ $printLockMsg }}">
          PDF
        </button>
      @endif

      {{-- BORROWER: submit (owner only) --}}
      @if($doc->status === 'draft' && $isOwner)
        <form method="POST" action="{{ route('requests.submit', $doc->id) }}">
          @csrf
          <button class="btn btn-sm btn-primary" type="submit">Submit</button>
        </form>
      @endif

      {{-- UNITADMIN: NOTE (only when submitted) --}}
      @if($doc->status === 'submitted' && $role === 'unitadmin')
        <form method="POST" action="{{ route('requests.note', $doc->id) }}">
          @csrf
          <button class="btn btn-sm btn-warning" type="submit">Note</button>
        </form>
      @endif

      {{-- ADMINISTRATOR: APPROVE (only when noted) --}}
      @if($doc->status === 'noted' && $role === 'administrator')
        <form method="POST" action="{{ route('requests.approve', $doc->id) }}">
          @csrf
          <button class="btn btn-sm btn-success" type="submit">Approve</button>
        </form>
      @endif

      {{-- ? ADMINISTRATOR: REJECT (submitted OR noted) --}}
      @if($canAdminReject)
        <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal">
          Reject
        </button>
      @endif

    </div>
  </div>

  <div class="card-body">

    {{-- show validation errors / messages --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    {{-- ? Updated info message (depends on type) --}}
    @if(!$canPrint && $doc->status !== 'rejected')
      <div class="alert alert-info">
        {!! $printInfoMsg !!}
        {!! $signatureMsg !!}
      </div>
    @endif

    @if($doc->status === 'rejected')
      <div class="alert alert-danger">
        <strong>Rejected:</strong> {{ $doc->rejection_reason }}
        <div class="mt-1 text-sm">
          Please check the line remarks below (if any), update the request, then submit again.
        </div>
      </div>
    @endif

    <p><strong>Department:</strong> {{ $doc->department }}</p>
    <p><strong>Name:</strong> {{ $doc->name }}</p>

    {{-- PAYMENT --}}
    @if($doc->type === 'payment')
      <hr>
      <h6>Payment Details</h6>
      <p><strong>Payable To:</strong> {{ optional($doc->paymentDetail)->payable_to }}</p>
      <p><strong>Address:</strong> {{ optional($doc->paymentDetail)->address }}</p>
      <p><strong>Date:</strong> {{ optional($doc->paymentDetail)->date }}</p>
    @endif

    {{-- LIQUIDATION --}}
    @if($doc->type === 'liquidation')
      <hr>
      <h6>Liquidation Details</h6>
      <p><strong>Week No:</strong> {{ $weekNo }}</p>
      <p><strong>Date Range:</strong> {{ $dateFrom }} to {{ $dateTo }}</p>

      <h6 class="mt-3">Purposes</h6>
      @if($doc->purposes && $doc->purposes->count())
        <ul class="mb-0">
          @foreach($doc->purposes as $p)
            <li>
              {{ $p->purpose }}
              @if($p->purpose==='others')
                - {{ $p->other_text }}
              @endif
            </li>
          @endforeach
        </ul>
      @else
        <div class="text-muted">No purposes selected.</div>
      @endif
    @endif

    {{-- BUSINESS TRIP --}}
    @if($doc->type === 'business_trip')
      <hr>
      <h6>Business Trip Details</h6>

      <p><strong>Driver's Name:</strong> {{ optional($bd)->driver_name }}</p>
      <p><strong>Date:</strong> {{ optional($bd)->trip_date }}</p>
      <p><strong>Vehicle Plate No.:</strong> {{ optional($bd)->vehicle_plate_no }}</p>
      <p><strong>Total Mileage (km):</strong> {{ optional($bd)->total_mileage_km }}</p>

      <p><strong>Speedometer Reading - Beginning:</strong> {{ optional($bd)->speedometer_begin }}</p>
      <p><strong>Speedometer Reading - End:</strong> {{ optional($bd)->speedometer_end }}</p>

      <p><strong>Time Out:</strong> {{ optional($bd)->time_out }}</p>
      <p><strong>Time In:</strong> {{ optional($bd)->time_in }}</p>

      <p><strong>Purpose:</strong> {{ optional($bd)->purpose }}</p>
      <p><strong>Checked By:</strong> {{ optional($bd)->checked_by }}</p>
      <p><strong>Noted By:</strong> {{ optional($bd)->noted_by }}</p>
    @endif

    {{-- ITEMS --}}
    @if($hasItems)
      <hr>
      <h6>Items</h6>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              @if($showItemDate) <th style="width:160px;">Date</th> @endif
              <th>Particulars</th>
              <th class="text-end" style="width:180px;">Amount</th>

              @if($doc->status === 'rejected')
                <th>Admin Remark</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($doc->items as $it)
              <tr>
                @if($showItemDate) <td>{{ $it->item_date }}</td> @endif
                <td>{{ $it->particulars }}</td>
                <td class="text-end">{{ number_format($it->amount, 2) }}</td>

                @if($doc->status === 'rejected')
                  <td class="text-danger">{{ $it->rejection_remark }}</td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $itemsColspan }}" class="text-center text-muted">
                  No items.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Totals --}}
      @if($doc->type === 'payment')
        <p class="text-end">
          <strong>Total:</strong> {{ number_format(optional($doc->paymentDetail)->total_amount ?? 0, 2) }}
        </p>
      @elseif($doc->type === 'liquidation')
        <p class="text-end">
          <strong>Reimbursement:</strong> {{ number_format(optional($doc->liquidationDetail)->reimbursement_amount ?? 0, 2) }}
        </p>
        <p class="text-end">
          <strong>Balance:</strong> {{ number_format(optional($doc->liquidationDetail)->ending_balance ?? 0, 2) }}
        </p>
      @endif
    @endif

    <hr>
    <h6>Approval Trail</h6>
    <ul class="mb-0">
      <li><strong>Submitted:</strong> {{ $doc->submitted_at ? $doc->submitted_at->format('M d, Y h:i A') : '—' }}</li>
      <li><strong>Noted:</strong> {{ $doc->noted_at ? $doc->noted_at->format('M d, Y h:i A') : '—' }}</li>
      <li><strong>Approved:</strong> {{ $doc->approved_at ? $doc->approved_at->format('M d, Y h:i A') : '—' }}</li>
    </ul>

    {{-- ? Updated final success message --}}
    @if($doc->type === 'business_trip' && $doc->status === 'noted')
      <div class="alert alert-success mt-3 mb-0">
        Noted. Business Trip form is now printable and downloadable as PDF.
      </div>
    @elseif($doc->status === 'approved')
      <div class="alert alert-success mt-3 mb-0">
        Approved. GM e-signature will be included on the print/PDF automatically.
      </div>
    @endif

  </div>
</div>

{{-- ? REJECT MODAL (Admin only, submitted/noted) --}}
@if($canAdminReject)
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="{{ route('requests.reject', $doc->id) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Reject Request: {{ $doc->request_no }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label"><strong>General reason</strong> (optional)</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="General rejection reason..."></textarea>
          </div>

          @if($hasItems)
            <hr>
            <h6 class="mb-2">Line remarks (per item)</h6>

            <div class="table-responsive">
              <table class="table table-bordered align-items-center">
                <thead>
                  <tr>
                    @if($showItemDate) <th style="width:140px;">Date</th> @endif
                    <th>Particulars</th>
                    <th style="width:140px;" class="text-end">Amount</th>
                    <th style="width:320px;">Admin Remark</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($doc->items as $it)
                    <tr>
                      @if($showItemDate) <td>{{ $it->item_date }}</td> @endif
                      <td>{{ $it->particulars }}</td>
                      <td class="text-end">{{ number_format($it->amount, 2) }}</td>
                      <td>
                        <textarea class="form-control"
                                  name="line_remarks[{{ $it->id }}]"
                                  rows="2"
                                  placeholder="Remark for this line...">{{ old("line_remarks.$it->id") }}</textarea>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info mb-0">
              This request type has no line items. Use the general reason above.
            </div>
          @endif
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection