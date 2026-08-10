{{--
    Shared account form.

    $user  — the account being edited, or null when creating
    $units — units available for a unit admin
    $roles — role value => label
--}}
@php $editing = isset($user) && $user; @endphp

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label for="name">Full name</label>
      <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
             value="{{ old('name', $editing ? $user->name : '') }}" required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
             value="{{ old('email', $editing ? $user->email : '') }}" required>
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label for="role">Role</label>
      <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
        @foreach ($roles as $value => $label)
          <option value="{{ $value }}"
            {{ old('role', $editing ? $user->role : '') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
      <small class="form-text text-muted">
        Administrators can reach everything. Unit admins are limited to their own unit. Borrowers can only book.
      </small>
      @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Only meaningful for a unit admin; shown/hidden by the script below. --}}
    <div class="form-group" id="unit-field">
      <label for="unit_id">Unit</label>
      <select class="form-control @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id">
        <option value="">Select Unit</option>
        @foreach ($units as $unit)
          <option value="{{ $unit->id }}"
            {{ (string) old('unit_id', $editing ? $user->unit_id : '') === (string) $unit->id ? 'selected' : '' }}>
            {{ $unit->name }}
          </option>
        @endforeach
      </select>
      <small class="form-text text-muted">Required for a unit admin, and ignored for other roles.</small>
      @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="password">
        Password
        @if($editing)<span class="text-muted">— leave blank to keep the current one</span>@endif
      </label>
      <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
             name="password" autocomplete="new-password" {{ $editing ? '' : 'required' }}>
      @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label for="password_confirmation">Confirm password</label>
      <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
             autocomplete="new-password" {{ $editing ? '' : 'required' }}>
    </div>

    <div class="form-group">
      <label for="phone">Contact number</label>
      <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
             value="{{ old('phone', $editing ? $user->phone : '') }}">
      @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label for="address">Address</label>
      <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address"
             value="{{ old('address', $editing ? $user->address : '') }}">
      @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
      <label for="city">City</label>
      <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city"
             value="{{ old('city', $editing ? $user->city : '') }}">
      @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>
</div>

@push('scripts')
<script>
    // The unit selector only applies to a unit admin. Hiding it for other roles
    // keeps the form honest about what is actually going to be saved.
    (function () {
        var role = document.getElementById('role');
        var unitField = document.getElementById('unit-field');
        if (!role || !unitField) return;

        function syncUnitField() {
            var isUnitAdmin = role.value === 'unitadmin';
            unitField.style.display = isUnitAdmin ? '' : 'none';
            document.getElementById('unit_id').required = isUnitAdmin;
        }

        role.addEventListener('change', syncUnitField);
        syncUnitField();
    })();
</script>
@endpush
