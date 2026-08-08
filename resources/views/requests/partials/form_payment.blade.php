<form method="POST" action="{{ route('requests.store') }}">
  @csrf
  <input type="hidden" name="type" value="payment">

  <!-- header fields... -->
  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Payable to</label>
      <input type="text" name="payable_to" class="form-control" value="{{ old('payable_to') }}" required>
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Date</label>
      <input type="date" name="date" class="form-control" value="{{ old('date') ?? now()->toDateString() }}">
    </div>
  </div>

  <div class="mt-3">
    <label class="form-control-label">Address</label>
    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
  </div>

  <hr>

  <h6 class="mb-2">Particulars</h6>

  <div id="payment-items">
    @php
      $items = old('items', [['particulars' => '', 'amount' => '']]);
    @endphp

    @foreach($items as $i => $item)
      <div class="row align-items-end mb-2 payment-row">
        <div class="col-md-8">
          <label class="form-control-label">Particulars</label>
          <input type="text"
                 name="items[{{ $i }}][particulars]"
                 class="form-control"
                 value="{{ $item['particulars'] ?? '' }}"
                 required>
        </div>

        <div class="col-md-3">
          <label class="form-control-label">Amount</label>
          <input type="number"
                 step="0.01"
                 min="0"
                 name="items[{{ $i }}][amount]"
                 class="form-control"
                 value="{{ $item['amount'] ?? '' }}"
                 required>
        </div>

        <div class="col-md-1">
          <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
        </div>
      </div>
    @endforeach
  </div>

  <button type="button" class="btn btn-secondary btn-sm" id="add-payment-row">+ Add Row</button>

  <hr>

  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary">Save Request</button>
  </div>
</form>

@push('scripts')
<script>
(function () {
  const container = document.getElementById('payment-items');
  const addBtn = document.getElementById('add-payment-row');
  if (!container || !addBtn) return;

  // Start nextIndex based on existing rows (important for old() repopulate)
  let nextIndex = container.querySelectorAll('.payment-row').length;

  function rowTemplate(index) {
    return `
      <div class="row align-items-end mb-2 payment-row">
        <div class="col-md-8">
          <label class="form-control-label">Particulars</label>
          <input type="text" name="items[${index}][particulars]" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-control-label">Amount</label>
          <input type="number" step="0.01" min="0" name="items[${index}][amount]" class="form-control" required>
        </div>
        <div class="col-md-1">
          <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
        </div>
      </div>
    `;
  }

  function reindex() {
    const rows = container.querySelectorAll('.payment-row');
    rows.forEach((row, idx) => {
      const p = row.querySelector('input[name^="items"][name$="[particulars]"]');
      const a = row.querySelector('input[name^="items"][name$="[amount]"]');
      if (p) p.name = `items[${idx}][particulars]`;
      if (a) a.name = `items[${idx}][amount]`;
    });
    nextIndex = rows.length;
  }

  addBtn.addEventListener('click', function () {
    container.insertAdjacentHTML('beforeend', rowTemplate(nextIndex));
    nextIndex++;
  });

  container.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
      e.target.closest('.payment-row')?.remove();
      reindex();
    }
  });
})();
</script>
@endpush