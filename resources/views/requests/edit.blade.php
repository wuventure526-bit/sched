@extends('layouts.user_type.auth')

@section('content')
@php
  $type = $doc->type;

  $isLiquidation   = ($type === 'liquidation');
  $isPayment       = ($type === 'payment');
  $isBusinessTrip  = ($type === 'business_trip');
  $isRevolvingFund = ($type === 'revolving_fund');

  $ld = $doc->liquidationDetail;
  $pd = $doc->paymentDetail;
  $bd = $doc->businessTripDetail;
  $rfd = $doc->revolvingFundDetail ?? null;

  // ? NEW RULE: editable when NOT noted and NOT approved
  $isLocked = in_array($doc->status, ['noted', 'approved'], true);

  // fallback (avoid blanks)
  $weekNo   = old('week_no', $doc->week_no ?? ($ld->week_no ?? ($rfd->week_no ?? '')));
  $dateFrom = old('date_from', $doc->date_from ?? ($ld->date_from ?? ($rfd->date_from ?? '')));
  $dateTo   = old('date_to', $doc->date_to ?? ($ld->date_to ?? ($rfd->date_to ?? '')));

  // ? items old() or existing (NOW used for liquidation, payment, revolving_fund)
  $items = old('items');
  if (!$items) {
      $items = $doc->items->map(function($it){
          return [
              // date input needs Y-m-d
              'date'        => $it->item_date ? \Carbon\Carbon::parse($it->item_date)->format('Y-m-d') : '',
              'particulars' => $it->particulars ?? '',
              'amount'      => $it->amount ?? '',
          ];
      })->toArray();
  }

  if (!is_array($items) || count($items) === 0) {
      $items = [['date' => '', 'particulars' => '', 'amount' => '']];
  }

  // purposes (liquidation only)
  $selectedPurposes = old('purposes', $doc->purposes ? $doc->purposes->pluck('purpose')->toArray() : []);
  $purposeOtherText = old(
      'purpose_other_text',
      optional($doc->purposes?->firstWhere('purpose','others'))->other_text ?? ''
  );

  $purposeOptions = [
    'business_travel_allowance' => 'Business Travel Allowance',
    'mobilization_installation' => 'Mobilization / Installation',
    'site_inspection' => 'Site Inspection',
    'representation' => 'Representation',
    'employees_benefit' => 'Employees Benefit',
    'others' => 'Others',
  ];

  // Helper: disabled attr when locked
  $disabled = $isLocked ? 'disabled' : '';

  // ? show date column for liquidation + revolving fund
  $showItemDate = $isLiquidation || $isRevolvingFund;
@endphp

<div class="card mx-3 mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h6 class="m-0">Edit Request: {{ $doc->request_no }} ({{ strtoupper($doc->type) }})</h6>
      <small>Status: <strong>{{ strtoupper($doc->status) }}</strong></small>
    </div>

    <div class="d-flex gap-2">
      <a href="{{ route('requests.show', $doc->id) }}" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
  </div>

  <div class="card-body">

    @if($isLocked)
      <div class="alert alert-danger">
        This request can no longer be edited because it is already <strong>{{ strtoupper($doc->status) }}</strong>.
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('requests.update', $doc->id) }}">
      @csrf
      @method('PUT')

      {{-- =========================
           COMMON HEADER FIELDS
           ========================= --}}
      <div class="row">
        <div class="col-md-6 mb-2">
          <label>Department</label>
          <input class="form-control" name="department" value="{{ old('department', $doc->department) }}" {{ $disabled }}>
        </div>

        <div class="col-md-6 mb-2">
          <label>Name</label>
          <input class="form-control" name="name" value="{{ old('name', $doc->name) }}" {{ $disabled }}>
        </div>

        {{-- LIQUIDATION HEADER --}}
        @if($isLiquidation)
          <div class="col-md-4 mb-2">
            <label>Week No</label>
            <input class="form-control" name="week_no" value="{{ $weekNo }}" {{ $disabled }}>
          </div>

          {{-- Form No is system generated so show read-only --}}
          <div class="col-md-4 mb-2">
            <label>Form No</label>
            <input class="form-control" value="{{ $doc->form_no ?? ($ld->form_no ?? '') }}" readonly>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date From</label>
            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date To</label>
            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}" {{ $disabled }}>
          </div>
        @endif

        {{-- REVOLVING FUND HEADER --}}
        @if($isRevolvingFund)
          <div class="col-md-4 mb-2">
            <label>Week No</label>
            <input class="form-control" name="week_no" value="{{ $weekNo }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date From</label>
            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date To</label>
            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}" {{ $disabled }}>
          </div>
        @endif
      </div>

      {{-- =========================
           LIQUIDATION FIELDS
           ========================= --}}
      @if($isLiquidation)
        <hr>

        <div class="mb-2"><strong>Purpose</strong></div>
        <div class="row">
          @foreach($purposeOptions as $k => $label)
            <div class="col-md-4">
              <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="purposes[]"
                       value="{{ $k }}"
                       id="p_{{ $k }}"
                       {{ in_array($k, $selectedPurposes) ? 'checked' : '' }}
                       {{ $disabled }}>
                <label class="form-check-label" for="p_{{ $k }}">{{ $label }}</label>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-2">
          <label>If Others, specify</label>
          <input class="form-control" name="purpose_other_text" value="{{ $purposeOtherText }}" {{ $disabled }}>
        </div>

        <hr>

        <div class="row">
          <div class="col-md-4 mb-2">
            <label>Amount of Cash Advance (per CV no.)</label>
            <input class="form-control" name="cash_advance_amount"
                   value="{{ old('cash_advance_amount', optional($ld)->cash_advance_amount) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date</label>
            <input type="date" class="form-control" name="cash_advance_date"
                   value="{{ old('cash_advance_date', optional($ld)->cash_advance_date) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Balance from Previous Liquidation</label>
            <input class="form-control" name="previous_balance"
                   value="{{ old('previous_balance', optional($ld)->previous_balance) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Starting Balance</label>
            <input class="form-control" name="starting_balance"
                   value="{{ old('starting_balance', optional($ld)->starting_balance) }}" {{ $disabled }}>
          </div>
        </div>
      @endif

      {{-- =========================
           REVOLVING FUND FIELDS
           ========================= --}}
      @if($isRevolvingFund)
        <hr>
        <h6>Revolving Fund Details</h6>

        <div class="row">
          <div class="col-md-4 mb-2">
            <label>Amount of Cash Advance (per CV no.)</label>
            <input class="form-control" name="cash_advance_amount"
                   value="{{ old('cash_advance_amount', optional($rfd)->cash_advance_amount) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date</label>
            <input type="date" class="form-control" name="cash_advance_date"
                   value="{{ old('cash_advance_date', optional($rfd)->cash_advance_date) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Balance from Previous Liquidation</label>
            <input class="form-control" name="previous_balance"
                   value="{{ old('previous_balance', optional($rfd)->previous_balance) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Starting Balance</label>
            <input class="form-control" name="starting_balance"
                   value="{{ old('starting_balance', optional($rfd)->starting_balance) }}" {{ $disabled }}>
          </div>
        </div>
      @endif

      {{-- =========================
           PAYMENT FIELDS
           ========================= --}}
      @if($isPayment)
        <hr>
        <h6>Payment Details</h6>

        <div class="row">
          <div class="col-md-6 mb-2">
            <label>Payable To</label>
            <input class="form-control" name="payable_to"
                   value="{{ old('payable_to', optional($pd)->payable_to) }}" required {{ $disabled }}>
          </div>

          <div class="col-md-6 mb-2">
            <label>Address</label>
            <input class="form-control" name="address"
                   value="{{ old('address', optional($pd)->address) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Date</label>
            <input type="date" class="form-control" name="date"
                   value="{{ old('date', optional($pd)->date) }}" {{ $disabled }}>
          </div>
        </div>
      @endif

      {{-- =========================
           BUSINESS TRIP FIELDS
           ========================= --}}
      @if($isBusinessTrip)
        <hr>
        <h6>Business Trip Details</h6>

        <div class="row">
          <div class="col-md-6 mb-2">
            <label>Driver Name</label>
            <input class="form-control" name="driver_name"
                   value="{{ old('driver_name', optional($bd)->driver_name) }}" required {{ $disabled }}>
          </div>

          <div class="col-md-3 mb-2">
            <label>Trip Date</label>
            <input type="date" class="form-control" name="trip_date"
                   value="{{ old('trip_date', optional($bd)->trip_date) }}" required {{ $disabled }}>
          </div>

          <div class="col-md-3 mb-2">
            <label>Vehicle Plate No</label>
            <input class="form-control" name="vehicle_plate_no"
                   value="{{ old('vehicle_plate_no', optional($bd)->vehicle_plate_no) }}" required {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Total Mileage (km)</label>
            <input class="form-control" name="total_mileage_km"
                   value="{{ old('total_mileage_km', optional($bd)->total_mileage_km) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Speedometer Begin</label>
            <input class="form-control" name="speedometer_begin"
                   value="{{ old('speedometer_begin', optional($bd)->speedometer_begin) }}" {{ $disabled }}>
          </div>

          <div class="col-md-4 mb-2">
            <label>Speedometer End</label>
            <input class="form-control" name="speedometer_end"
                   value="{{ old('speedometer_end', optional($bd)->speedometer_end) }}" {{ $disabled }}>
          </div>

          <div class="col-md-3 mb-2">
            <label>Time Out</label>
            <input type="time" class="form-control" name="time_out"
                   value="{{ old('time_out', optional($bd)->time_out) }}" {{ $disabled }}>
          </div>

          <div class="col-md-3 mb-2">
            <label>Time In</label>
            <input type="time" class="form-control" name="time_in"
                   value="{{ old('time_in', optional($bd)->time_in) }}" {{ $disabled }}>
          </div>

          <div class="col-md-6 mb-2">
            <label>Purpose</label>
            <input class="form-control" name="purpose"
                   value="{{ old('purpose', optional($bd)->purpose) }}" {{ $disabled }}>
          </div>

          <div class="col-md-6 mb-2">
            <label>Checked By</label>
            <input class="form-control" name="checked_by"
                   value="{{ old('checked_by', optional($bd)->checked_by) }}" {{ $disabled }}>
          </div>

          <div class="col-md-6 mb-2">
            <label>Noted By</label>
            <input class="form-control" name="noted_by"
                   value="{{ old('noted_by', optional($bd)->noted_by) }}" {{ $disabled }}>
          </div>
        </div>
      @endif

      {{-- =========================
           ITEMS (liquidation/payment/revolving_fund)
           ========================= --}}
      @if(!$isBusinessTrip)
        <hr>

        <div class="d-flex justify-content-between align-items-center">
          <strong>Items</strong>
          <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()" {{ $isLocked ? 'disabled' : '' }}>
            Add Row
          </button>
        </div>

        <div class="table-responsive mt-2">
          <table class="table table-bordered" id="itemsTable">
            <thead>
              <tr>
                @if($showItemDate)
                  <th style="width:160px">Date</th>
                @endif
                <th>Particulars</th>
                <th style="width:180px">Amount</th>
                <th style="width:70px"></th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $i => $row)
                <tr>
                  @if($showItemDate)
                    <td>
                      <input type="date" name="items[{{ $i }}][date]" class="form-control"
                             value="{{ $row['date'] ?? '' }}" {{ $disabled }}>
                    </td>
                  @endif

                  <td>
                    <input name="items[{{ $i }}][particulars]" class="form-control" required
                           value="{{ $row['particulars'] ?? '' }}" {{ $disabled }}>
                  </td>

                  <td>
                    <input name="items[{{ $i }}][amount]" class="form-control" required
                           value="{{ $row['amount'] ?? '' }}" {{ $disabled }}>
                  </td>

                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)" {{ $isLocked ? 'disabled' : '' }}>X</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

      {{-- Submit --}}
      @if(!$isLocked)
        <button class="btn btn-primary mt-2" type="submit">Update</button>
      @endif
    </form>
  </div>
</div>

<script>
let rowIndex = {{ count($items) }};
const showItemDate = {!! $showItemDate ? 'true' : 'false' !!};
const isLocked = {!! $isLocked ? 'true' : 'false' !!};

function addRow(){
  if (isLocked) return;

  const tbody = document.querySelector('#itemsTable tbody');
  const tr = document.createElement('tr');

  tr.innerHTML = `
    ${showItemDate ? `<td><input type="date" name="items[${rowIndex}][date]" class="form-control"></td>` : ``}
    <td><input name="items[${rowIndex}][particulars]" class="form-control" required></td>
    <td><input name="items[${rowIndex}][amount]" class="form-control" required></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">X</button></td>
  `;

  tbody.appendChild(tr);
  rowIndex++;
}

function removeRow(btn){
  if (isLocked) return;

  const tbody = document.querySelector('#itemsTable tbody');
  if(tbody.children.length === 1) return;
  btn.closest('tr').remove();
}
</script>
@endsection