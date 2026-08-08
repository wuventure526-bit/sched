@extends('layouts.user_type.auth')

@section('content')
@include('components.notifications')

<div class="card mx-3 mb-3">
    <div class="card-header pb-3">
        <h6 class="m-0">Edit Item</h6>
        <p class="text-sm mb-0">Feel empowered to modify this item by changing its status, adjusting the quantity, or making any necessary edits to its details.</p>
    </div>
    <div class="card-body pt-0">
        <form method="POST" action="{{ route('items.update', $item->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    {{-- Only administrators may move an item to another unit. --}}
                    @if ($units->isNotEmpty())
                        <div class="form-group">
                            <label for="unit_id">Unit</label>
                            <select class="form-control" id="unit_id" name="unit_id" required>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $unit->id == $item->unit_id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->id == $item->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" class="form-control" id="brand" name="brand" value="{{ old('brand', $item->brand) }}" required>
                        @error('brand')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ old('serial_number', $item->serial_number) }}">
                        @error('serial_number')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="photo">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                        @error('photo')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity', $item->quantity) }}" min="0" required>
                        @error('quantity')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        @php
                            // "empty" is derived from the stock level, so it is not offered
                            // here — an empty item is still in service, just out of stock.
                            $serviceState = old('status', $item->status) === 'not available'
                                ? 'not available'
                                : 'available';
                        @endphp
                        <select class="form-control" id="status" name="status" required>
                            <option value="available" {{ $serviceState === 'available' ? 'selected' : '' }}>In service</option>
                            <option value="not available" {{ $serviceState === 'not available' ? 'selected' : '' }}>Out of service</option>
                        </select>
                        <small class="form-text text-muted">
                            In-service items show as <strong>Available</strong>, and switch to
                            <strong>Empty</strong> on their own once the quantity reaches zero.
                        </small>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ $item->description }}</textarea>
                        <small id="character_count" class="form-text text-muted">Type to check remaining character</small>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn bg-gradient-primary me-2">Update Item</button>
                    <a href="{{ route('items.index') }}" class="btn bg-gradient-info">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
