<form method="POST" action="{{ route('requests.store') }}">
  @csrf
  <input type="hidden" name="type" value="business_trip">

  {{-- GLOBAL ERRORS --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Driver's Name <span class="text-danger">*</span></label>
      <input
        type="text"
        name="driver_name"
        class="form-control @error('driver_name') is-invalid @enderror"
        value="{{ old('driver_name') }}"
        required
      >
      @error('driver_name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Date <span class="text-danger">*</span></label>
      <input
        type="date"
        name="trip_date"
        class="form-control @error('trip_date') is-invalid @enderror"
        value="{{ old('trip_date', now()->toDateString()) }}"
        required
      >
      @error('trip_date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Vehicle Plate No. <span class="text-danger">*</span></label>
      <input
        type="text"
        name="vehicle_plate_no"
        class="form-control @error('vehicle_plate_no') is-invalid @enderror"
        value="{{ old('vehicle_plate_no') }}"
        required
      >
      @error('vehicle_plate_no')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Total Mileage (km)</label>
      <input
        type="number"
        step="0.01"
        min="0"
        name="total_mileage_km"
        class="form-control @error('total_mileage_km') is-invalid @enderror"
        value="{{ old('total_mileage_km') }}"
        placeholder="Optional"
      >
      @error('total_mileage_km')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
      <small class="text-muted">Tip: You can leave this blank if you will compute it later.</small>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Speedometer Reading - Beginning</label>
      <input
        type="text"
        name="speedometer_begin"
        class="form-control @error('speedometer_begin') is-invalid @enderror"
        value="{{ old('speedometer_begin') }}"
        placeholder="e.g. 12345"
      >
      @error('speedometer_begin')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Speedometer Reading - End</label>
      <input
        type="text"
        name="speedometer_end"
        class="form-control @error('speedometer_end') is-invalid @enderror"
        value="{{ old('speedometer_end') }}"
        placeholder="e.g. 12410"
      >
      @error('speedometer_end')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Time Out</label>
      <input
        type="time"
        name="time_out"
        class="form-control @error('time_out') is-invalid @enderror"
        value="{{ old('time_out') }}"
      >
      @error('time_out')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Time In</label>
      <input
        type="time"
        name="time_in"
        class="form-control @error('time_in') is-invalid @enderror"
        value="{{ old('time_in') }}"
      >
      @error('time_in')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-12">
      <label class="form-control-label">Purpose</label>
      <textarea
        name="purpose"
        class="form-control @error('purpose') is-invalid @enderror"
        rows="3"
        placeholder="Optional"
      >{{ old('purpose') }}</textarea>
      @error('purpose')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-md-6">
      <label class="form-control-label">Checked By</label>
      <input
        type="text"
        name="checked_by"
        class="form-control @error('checked_by') is-invalid @enderror"
        value="{{ old('checked_by') }}"
        placeholder="Optional"
      >
      @error('checked_by')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-control-label">Noted By</label>
      <input
        type="text"
        name="noted_by"
        class="form-control @error('noted_by') is-invalid @enderror"
        value="{{ old('noted_by') }}"
        placeholder="Optional"
      >
      @error('noted_by')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <hr>
  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary">
      Save Request
    </button>
  </div>
</form>