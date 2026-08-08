<form method="POST" action="{{ route('requests.store') }}">
  @csrf
  <input type="hidden" name="type" value="liquidation" />

  <div class="row">
    <div class="col-md-6 mb-2">
      <label>Department</label>
      <input class="form-control" name="department" value="{{ old('department') }}">
      @error('department') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-6 mb-2">
      <label>Name</label>
      <input class="form-control" name="name" value="{{ old('name') }}">
      @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-4 mb-2">
      <label>Week No</label>
      <input class="form-control" name="week_no" value="{{ old('week_no') }}">
      @error('week_no') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    {{-- ? FORM NO REMOVED (system-generated in controller) --}}

    <div class="col-md-4 mb-2">
      <label>Date From</label>
      <input type="date" class="form-control" name="date_from" value="{{ old('date_from') }}">
      @error('date_from') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-4 mb-2">
      <label>Date To</label>
      <input type="date" class="form-control" name="date_to" value="{{ old('date_to') }}">
      @error('date_to') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <hr>

  <div class="mb-2"><strong>Purpose</strong></div>

  @php
    $purposeOptions = [
      'business_travel_allowance' => 'Business Travel Allowance',
      'mobilization_installation' => 'Mobilization / Installation',
      'site_inspection'          => 'Site Inspection',
      'representation'          => 'Representation',
      'employees_benefit'       => 'Employees Benefit',
      'others'                  => 'Others',
    ];

    $oldPurposes = old('purposes', []);
  @endphp

  <div class="row">
    @foreach($purposeOptions as $k => $label)
      <div class="col-md-4">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            name="purposes[]"
            value="{{ $k }}"
            id="p_{{ $k }}"
            {{ in_array($k, $oldPurposes) ? 'checked' : '' }}
          >
          <label class="form-check-label" for="p_{{ $k }}">{{ $label }}</label>
        </div>
      </div>
    @endforeach
  </div>

  @error('purposes') <small class="text-danger">{{ $message }}</small> @enderror

  <div class="mt-2">
    <label>If Others, specify</label>
    <input class="form-control" name="purpose_other_text" value="{{ old('purpose_other_text') }}">
    @error('purpose_other_text') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <hr>

  <div class="row">
    <div class="col-md-4 mb-2">
      <label>Amount of Cash Advance (per CV no.)</label>
      <input class="form-control" name="cash_advance_amount" value="{{ old('cash_advance_amount') }}">
      @error('cash_advance_amount') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-4 mb-2">
      <label>Date</label>
      <input type="date" class="form-control" name="cash_advance_date" value="{{ old('cash_advance_date') }}">
      @error('cash_advance_date') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-4 mb-2">
      <label>Balance from Previous Liquidation</label>
      <input class="form-control" name="previous_balance" value="{{ old('previous_balance') }}">
      @error('previous_balance') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-4 mb-2">
      <label>Starting Balance</label>
      <input class="form-control" name="starting_balance" value="{{ old('starting_balance') }}">
      @error('starting_balance') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
  </div>

  <hr>

  <div class="d-flex justify-content-between align-items-center">
    <strong>Items</strong>
    <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">Add Row</button>
  </div>

  <div class="table-responsive mt-2">
    <table class="table table-bordered" id="itemsTable">
      <thead>
        <tr>
          <th style="width:160px">Date</th>
          <th>Particulars</th>
          <th style="width:180px">Amount</th>
          <th style="width:70px"></th>
        </tr>
      </thead>

      <tbody>
        @php
          $oldItems = old('items');
          $oldItems = is_array($oldItems) && count($oldItems) ? $oldItems : [['date' => null, 'particulars' => null, 'amount' => null]];
        @endphp

        @foreach($oldItems as $i => $row)
          <tr>
            <td>
              <input type="date" name="items[{{ $i }}][date]" class="form-control"
                     value="{{ $row['date'] ?? '' }}">
              @error("items.$i.date") <small class="text-danger">{{ $message }}</small> @enderror
            </td>

            <td>
              <input name="items[{{ $i }}][particulars]" class="form-control" required
                     value="{{ $row['particulars'] ?? '' }}">
              @error("items.$i.particulars") <small class="text-danger">{{ $message }}</small> @enderror
            </td>

            <td>
              <input name="items[{{ $i }}][amount]" class="form-control" required
                     value="{{ $row['amount'] ?? '' }}">
              @error("items.$i.amount") <small class="text-danger">{{ $message }}</small> @enderror
            </td>

            <td class="text-center">
              <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">X</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    @error('items') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <button class="btn btn-primary mt-2" type="submit">Save Draft</button>
</form>

<script>
let rowIndex = document.querySelectorAll('#itemsTable tbody tr').length;

function addRow(){
  const tbody = document.querySelector('#itemsTable tbody');
  const tr = document.createElement('tr');

  tr.innerHTML = `
    <td><input type="date" name="items[${rowIndex}][date]" class="form-control"></td>
    <td><input name="items[${rowIndex}][particulars]" class="form-control" required></td>
    <td><input name="items[${rowIndex}][amount]" class="form-control" required></td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">X</button>
    </td>
  `;

  tbody.appendChild(tr);
  rowIndex++;
}

function removeRow(btn){
  const tbody = document.querySelector('#itemsTable tbody');
  if(tbody.children.length === 1) return;
  btn.closest('tr').remove();
}
</script>