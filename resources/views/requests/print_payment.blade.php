
@include('requests.partials.print_header')
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .title { text-align:center; font-size:18px; font-weight:bold; margin-bottom:10px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #000; padding:6px; vertical-align: top; }
    .no-border td { border:none; }
    .right { text-align:right; }

    /* Signature layout */
    .signature-box{
      height: 130px;
      text-align: center;
      vertical-align: bottom;
      padding-top: 10px;
      position: relative;
    }

    /* Signature image overlaps the name */
    .sig-img{
      max-height: 90px;
      max-width: 220px;
      display: block;
      margin: 0 auto;
      position: relative;
      top: 28px; /* pushes signature downward to overlap name */
    }

    /* Name sits under signature, ABOVE the line */
    .name{
      font-weight: bold;
      margin-top: -14px; /* pulls name upward under the signature */
      line-height: 1.1;
    }

    /* Line should be BELOW name */
    .line{
      border-top: 1px solid #000;
      margin-top: 4px;
      padding-top: 2px;
    }

    .watermark {
      position: fixed;
      top: 45%;
      left: 15%;
      font-size: 80px;
      color: rgba(200,0,0,0.15);
      transform: rotate(-30deg);
      z-index: -1;
      white-space: nowrap;
    }
  </style>
</head>
<body>

{{-- WATERMARK IF NOT APPROVED --}}
@if($doc->status !== 'approved')
  <div class="watermark">FOR APPROVAL</div>
@endif

<div class="title">REQUEST FOR PAYMENT</div>

<table class="no-border">
  <tr>
    <td style="width:70%">
      <strong>Please issue check payable to:</strong>
      {{ optional($doc->paymentDetail)->payable_to ?? '—' }}
    </td>
    <td class="right">
      <strong>Date:</strong>
      {{ optional(optional($doc->paymentDetail)->date)?->format('Y-m-d') ?? '—' }}
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <strong>Address:</strong> {{ optional($doc->paymentDetail)->address ?? '—' }}
    </td>
  </tr>
</table>

<br>

<table>
  <thead>
    <tr>
      <th>PARTICULARS</th>
      <th style="width:180px">AMOUNT</th>
    </tr>
  </thead>
  <tbody>
    @foreach($doc->items as $it)
    <tr>
      <td>{{ $it->particulars }}</td>
      <td class="right">{{ number_format($it->amount, 2) }}</td>
    </tr>
    @endforeach

    <tr>
      <td class="right"><strong>TOTAL</strong></td>
      <td class="right">
        <strong>{{ number_format(optional($doc->paymentDetail)->total_amount ?? 0, 2) }}</strong>
      </td>
    </tr>
  </tbody>
</table>

<br><br>

<table class="no-border">
  <tr>

    {{-- PREPARED BY (REQUESTER) --}}
    <td class="signature-box">
      <strong>Prepared by:</strong><br><br>

      {{-- (optional) add prepared-by signature later --}}
      <div class="name">
        {{ optional($doc->user)->name ?? '—' }}
      </div>

      <div class="line"></div>
      Signature Over Printed Name
    </td>

    {{-- NOTED BY = Department Head --}}
    <td class="signature-box">
      <strong>Noted by:</strong><br><br>

      {{-- If you later want department head signature, add it like approved --}}
      <div class="name">
        {{ $doc->noted_at ? (optional($doc->notedByUser)->name ?? '—') : ' ' }}
      </div>

      <div class="line"></div>
      Department Head
    </td>

    {{-- APPROVED BY = General Manager --}}
    <td class="signature-box">
      <strong>Approved by:</strong><br><br>

      @if($doc->approved_at)

        @php
          $sigPath = public_path('signatures/sign.png');
          $sigBase64 = file_exists($sigPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath))
            : null;
        @endphp

        @if($sigBase64)
          <img class="sig-img" src="{{ $sigBase64 }}" alt="Signature">
        @endif

        <div class="name">
          {{ optional($doc->approvedByUser)->name ?? '—' }}
        </div>

      @else
        {{-- keep spacing consistent when not approved --}}
        <div style="height:55px;"></div>
        <div class="name">&nbsp;</div>
      @endif

      <div class="line"></div>
      General Manager
    </td>

  </tr>
</table>

</body>
</html>