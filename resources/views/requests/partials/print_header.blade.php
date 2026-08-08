@php
  $path = public_path('images/graphicstar_header.png');
  $type = pathinfo($path, PATHINFO_EXTENSION);
  $data = file_get_contents($path);
  $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
@endphp

<div style="width:100%; margin-bottom:15px;">
  <img src="{{ $base64 }}" style="width:100%; max-height:80px; height:auto;" alt="GraphicStar Header">
  <div style="height:4px; background:#f07c00; margin-top:5px;"></div>
  <div style="height:6px; background:#1e3a8a;"></div>
</div>