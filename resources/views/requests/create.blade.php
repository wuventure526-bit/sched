@extends('layouts.user_type.auth')

@section('content')
<div class="card mx-3 mb-4">
  <div class="card-header">
    <h6 class="m-0">Create Request</h6>
  </div>

  <div class="card-body">
    <p>Select request type:</p>

    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-primary" href="{{ url('/requests/create?type=liquidation') }}">Liquidation</a>
      <a class="btn btn-primary" href="{{ url('/requests/create?type=payment') }}">Request for Payment</a>
      <a class="btn btn-primary" href="{{ url('/requests/create?type=business_trip') }}">Business Trip</a>

      {{-- ? NEW --}}
      <a class="btn btn-primary" href="{{ url('/requests/create?type=revolving_fund') }}">Revolving Fund</a>
    </div>

    @php $type = request('type'); @endphp
    <hr>

    @if($type === 'liquidation')
      @include('requests.partials.form_liquidation')

    @elseif($type === 'payment')
      @include('requests.partials.form_payment')

    @elseif($type === 'business_trip')
      @include('requests.partials.form_business_trip')

    {{-- ? NEW --}}
    @elseif($type === 'revolving_fund')
      @include('requests.partials.form_revolving_fund')

    @else
      <div class="alert alert-info">Choose a type above to load the form.</div>
    @endif
  </div>
</div>
@endsection