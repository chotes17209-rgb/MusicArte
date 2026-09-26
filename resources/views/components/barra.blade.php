@props(['porcentaje' => 0, 'tono' => null])
@php
    $p = max(0, min(100, (float) $porcentaje));
    $tono = $tono ?? ($p >= 85 ? 'verde' : ($p >= 65 ? 'ambar' : 'rojo'));
@endphp
<div class="barra-fila">
    <div class="barra"><span class="tono-{{ $tono }}" style="width: {{ $p }}%"></span></div>
    <span class="barra-num">{{ rtrim(rtrim(number_format($p, 1), '0'), '.') }}%</span>
</div>
