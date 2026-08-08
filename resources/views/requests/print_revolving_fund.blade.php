@include('requests.partials.print_header')
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Revolving Fund Form</title>

<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }

.header { text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 10px; }

.line-fill { border-bottom: 1px solid #000; display: inline-block; min-width: 200px; }

table { width: 100%; border-collapse: collapse; margin-top: 10px; }

th, td {
  border: 1px solid #000;
  padding: 4px;
  font-size: 12px;
}

.no-border td { border: none; }

/* ========= Signature layout (same idea as your payment template) ========= */
.signature-box{
  height: 130px;
  text-align: center;
  vertical-align: bottom;
  padding-top: 10px;
  position: relative;
}

.sig-img{
  max-height: 90px;
  max-width: 220px;
  display: block;
  margin: 0 auto;
  position: relative;
  top: 28px; /* pushes signature down to overlap name */
}

.name{
  font-weight: bold;
  margin-top: -14px; /* pulls name upward under the signature */
  line-height: 1.1;
  word-wrap: break-word;
}

.sig-line{
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

@php
  // Revolving Fund detail (make sure controller loads revolvingFundDetail)
  $detail = $doc->revolvingFundDetail ?? null;

  // Fallback fix for header values (some are stored in request_documents too)
  $weekNo   = $doc->week_no   ?? ($detail->week_no   ?? '');
  $dateFrom = $doc->date_from ?? ($detail->date_from ?? '');
  $dateTo   = $doc->date_to   ?? ($detail->date_to   ?? '');

  // Totals
  $reimbursement = (float) $doc->items->sum('amount');
  $startingBalance = (float) ($detail->starting_balance ?? 0);
  $balance = $startingBalance - $reimbursement;

  // Signature base64 (only used if approved)
  $sigBase64 = null;
  if ($doc->approved_at) {
      // Change this path if you store per-user signature
      $sigPath = public_path('signatures/sign.png');

      if (file_exists($sigPath)) {
          $sigBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($sigPath));
      }
  }
@endphp

<body onload="window.print()">

{{-- OPTIONAL: watermark when not approved --}}
@if($doc->status !== 'approved')
  <div class="watermark">FOR APPROVAL</div>
@endif

<div class="header">REVOLVING FUND FORM</div>

<table class="no-border">
  <tr>
    <td>Department: <span class="line-fill">{{ $doc->department ?? '' }}</span></td>
    <td>Week No: <span class="line-fill">{{ $weekNo }}</span></td>
    <td>No: <span class="line-fill">{{ $doc->request_no ?? '' }}</span></td>
  </tr>

  <tr>
    <td>Name: <span class="line-fill">{{ $doc->name ?? '' }}</span></td>
    <td colspan="2">
      Date from:
      <span class="line-fill">{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('Y-m-d') : '' }}</span>
      to:
      <span class="line-fill">{{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('Y-m-d') : '' }}</span>
    </td>
  </tr>
</table>

<br>

<strong>Amount of Cash Advance:</strong> {{ $detail ? number_format((float)$detail->cash_advance_amount, 2) : '' }} <br>
<strong>Cash Advance Date:</strong> {{ $detail && $detail->cash_advance_date ? \Carbon\Carbon::parse($detail->cash_advance_date)->format('Y-m-d') : '' }} <br>
<strong>Previous Balance:</strong> {{ $detail ? number_format((float)$detail->previous_balance, 2) : '' }} <br>
<strong>Starting Balance:</strong> {{ $detail ? number_format((float)$detail->starting_balance, 2) : '' }}

<br><br>

<table>
  <thead>
    <tr>
      <th style="width:150px">Date</th>
      <th>Particulars</th>
      <th style="width:150px">Amount</th>
    </tr>
  </thead>

  <tbody>
    @forelse($doc->items as $item)
      <tr>
        <td>{{ $item->item_date ? \Carbon\Carbon::parse($item->item_date)->format('Y-m-d') : '' }}</td>
        <td>{{ $item->particulars }}</td>
        <td style="text-align:right">{{ number_format((float)$item->amount, 2) }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="3" style="text-align:center">No items.</td>
      </tr>
    @endforelse
  </tbody>
</table>

<br>

<table class="no-border" style="width: 380px; margin-left:auto;">
  <tr>
    <td style="text-align:right; width: 230px;"><strong>Reimbursement Amount</strong></td>
    <td style="border:1px solid #000; text-align:right; width: 150px;">
      {{ number_format($reimbursement, 2) }}
    </td>
  </tr>

  <tr>
    <td style="text-align:right;"><strong>Balance</strong></td>
    <td style="border:1px solid #000; text-align:right;">
      {{ number_format($balance, 2) }}
    </td>
  </tr>
</table>

<br><br>

<table class="no-border">
  <tr>
    {{-- PREPARED BY --}}
    <td class="signature-box">
      <strong>Prepared by:</strong><br><br>
      <div class="name">{{ optional($doc->user)->name ?? '—' }}</div>
      <div class="sig-line"></div>
      Signature Over Printed Name
    </td>

    {{-- NOTED BY --}}
    <td class="signature-box">
      <strong>Noted by:</strong><br><br>
      <div class="name">
        {{ $doc->noted_at ? (optional($doc->notedByUser)->name ?? '—') : ' ' }}
      </div>
      <div class="sig-line"></div>
      Department Head
    </td>

    {{-- APPROVED BY (WITH ESIGNATURE WHEN APPROVED) --}}
    <td class="signature-box">
      <strong>Approved by:</strong><br><br>

      @if($doc->approved_at)
        @if($sigBase64)
          <img class="sig-img" src="{{ $sigBase64 }}" alt="Signature">
        @endif

        <div class="name">{{ optional($doc->approvedByUser)->name ?? '—' }}</div>
      @else
        <div style="height:55px;"></div>
        <div class="name">&nbsp;</div>
      @endif

      <div class="sig-line"></div>
      General Manager
    </td>
  </tr>
</table>

</body>
</html>