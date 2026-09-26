@props(['label', 'valor', 'tono' => null, 'detalle' => null])
{{-- Indicador: etiqueta arriba, numero grande, detalle opcional abajo. tono: verde | rojo | ambar | acento --}}
<div {{ $attributes->merge(['class' => 'stat']) }}>
    <div class="stat-label">{{ $label }}</div>
    <div class="stat-valor {{ $tono ? 'tono-'.$tono : '' }}">{{ $valor }}</div>
    @if($detalle)<div class="stat-detalle">{{ $detalle }}</div>@endif
</div>
