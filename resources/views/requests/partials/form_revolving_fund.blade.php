@php
  // default items (revolving fund has Date/Particulars/Amount like liquidation)
  $items = old('items');

  if (!$items || !is_array($items) || count($items) === 0) {
      $items = [
          ['date' => '', 'particulars' => '', 'amount' => '']
      ];
  }
@endphp

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('requests.store') }}">
  @csrf

  {{-- IMPORTANT: identify request type --}}
  <input type="hidden" name="type" value="revolving_fund">

  <div class="row">
    <div class="col-md-6 mb-2">
      <label>Department</label>
      <input class="form-control" name="department" value="{{ old('department') }}">
    </div>

    <div class="col-md-6 mb-2">
      <label>Name</label>
      <input class="form-control" name="name" value="{{ old('name') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Week No</label>
      <input class="form-control" name="week_no" value="{{ old('week_no') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Date From</label>
      <input type="date" class="form-control" name="date_from" value="{{ old('date_from') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Date To</label>
      <input type="date" class="form-control" name="date_to" value="{{ old('date_to') }}">
    </div>
  </div>

  <hr>

  <div class="row">
    <div class="col-md-4 mb-2">
      <label>Amount of Cash Advance (per CV no.)</label>
      <input class="form-control" name="cash_advance_amount" value="{{ old('cash_advance_amount') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Date</label>
      <input type="date" class="form-control" name="cash_advance_date" value="{{ old('cash_advance_date') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Balance From Previous Liquidation</label>
      <input class="form-control" name="previous_balance" value="{{ old('previous_balance') }}">
    </div>

    <div class="col-md-4 mb-2">
      <label>Starting Balance</label>
      <input class="form-control" name="starting_balance" value="{{ old('starting_balance') }}">
    </div>
  </div>

  <hr>

  <div class="d-flex justify-content-between align-items-center">
    <strong>Items</strong>
    <button type="button" class="btn btn-sm btn-secondary" onclick="rfAddRow()">Add Row</button>
  </div>

  <div class="table-responsive mt-2">
    <table class="table table-bordered" id="rfItemsTable">
      <thead>
        <tr>
          <th style="width:160px">Date</th>
          <th>Particulars</th>
          <th style="width:180px">Amount</th>
          <th style="width:70px"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $i => $row)
          <tr>
            <td>
              <input type="date" name="items[{{ $i }}][date]" class="form-control"
                     value="{{ $row['date'] ?? '' }}">
            </td>
            <td>
              <input name="items[{{ $i }}][particulars]" class="form-control" required
                     value="{{ $row['particulars'] ?? '' }}">
            </td>
            <td>
              <input name="items[{{ $i }}][amount]" class="form-control" required
                     value="{{ $row['amount'] ?? '' }}">
            </td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-danger" onclick="rfRemoveRow(this)">X</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <button class="btn btn-primary mt-2" type="submit">Save as Draft</button>
</form>

<script>
let rfRowIndex = {{ count($items) }};

function rfAddRow(){
  const tbody = document.querySelector('#rfItemsTable tbody');
  const tr = document.createElement('tr');

  tr.innerHTML = `
    <td><input type="date" name="items[${rfRowIndex}][date]" class="form-control"></td>
    <td><input name="items[${rfRowIndex}][particulars]" class="form-control" required></td>
    <td><input name="items[${rfRowIndex}][amount]" class="form-control" required></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="rfRemoveRow(this)">X</button></td>
  `;

  tbody.appendChild(tr);
  rfRowIndex++;
}

function rfRemoveRow(btn){
  const tbody = document.querySelector('#rfItemsTable tbody');
  if(tbody.children.length === 1) return;
  btn.closest('tr').remove();
}
</script>