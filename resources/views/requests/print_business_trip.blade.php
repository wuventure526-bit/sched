@include('requests.partials.print_header')

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
  font-family: DejaVu Sans, Arial, sans-serif;
  font-size: 12px;
}

/* Title */
.title {
  text-align: center;
  font-size: 16px;
  font-weight: bold;
  margin: 10px 0 20px 0;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
}

td {
  padding: 8px 6px;
  vertical-align: top;
}

.lbl {
  font-weight: bold;
}

/* -------- LINE STYLES -------- */
.line {
  border-bottom: 1px solid #000;
  display: inline-block;
  height: 14px;
  padding: 0 3px;
  vertical-align: bottom;
}

/* Sizes */
.line-sm { width: 140px; }
.line-md { width: 200px; }
.line-lg { width: 280px; }

/* Purpose box */
.purpose-box {
  border: 1px solid #000;
  min-height: 60px;
  padding: 6px;
  margin-top: 6px;
}
</style>
</head>

<body onload="window.print()">

@php $d = $doc->businessTripDetail; @endphp

<div class="title">OFFICIAL BUSINESS TRIP FORM</div>

<table>

  <!-- DRIVER + DATE -->
  <tr>
    <td style="width:60%">
      <span class="lbl">Driver's Name :</span>
      <span class="line line-lg">{{ $d->driver_name ?? '' }}</span>
    </td>

    <td style="width:40%">
      <span class="lbl">Date :</span>
      <span class="line line-md">
        {{ optional($d->trip_date)?->format('Y-m-d') ?? '' }}
      </span>
    </td>
  </tr>

  <!-- VEHICLE + TIME -->
  <tr>
    <td>
      <span class="lbl">Vehicle Plate No. :</span>
      <span class="line line-md">{{ $d->vehicle_plate_no ?? '' }}</span>
    </td>

    <td>
      <div style="text-align:center;font-weight:bold;margin-bottom:4px;">
        Time
      </div>

      Out :
      <span class="line line-sm">{{ $d->time_out ?? '' }}</span>
      <br><br>

      In :
      <span class="line line-sm">{{ $d->time_in ?? '' }}</span>
    </td>
  </tr>

  <!-- SPEEDOMETER + MILEAGE -->
  <tr>
    <td>
      <span class="lbl">Speedometer Reading :</span>
      <br><br>

      Beginning :
      <span class="line line-md">
        {{ $d->speedometer_begin ?? '' }}
      </span>
      <br><br>

      End :
      <span class="line line-md">
        {{ $d->speedometer_end ?? '' }}
      </span>
    </td>

    <td>
      <span class="lbl">Total Mileage :</span>
      <span class="line line-sm">
        {{ $d->total_mileage_km ?? '' }}
      </span>
      km
    </td>
  </tr>

  <!-- PURPOSE -->
  <tr>
    <td colspan="2">
      <span class="lbl">Purpose :</span>
      <div class="purpose-box">
        {{ $d->purpose ?? '' }}
      </div>
    </td>
  </tr>

  <!-- SIGNATURES -->
  <tr>
    <td>
      <span class="lbl">Checked by :</span>
      <span class="line line-lg">
        {{ $d->checked_by ?? '' }}
      </span>
    </td>

    <td>
      <span class="lbl">Noted by :</span>
      <span class="line line-lg">
        {{ $d->noted_by ?? '' }}
      </span>
    </td>
  </tr>

</table>

</body>
</html>