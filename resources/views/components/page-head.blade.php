@props(['titulo', 'subtitulo' => null, 'volver' => null, 'volverTexto' => 'Volver'])
{{-- Encabezado de pagina: volver (opcional), titulo, descripcion y acciones/filtros a la derecha. --}}
<div class="page-head">
    <div class="page-head-texto">
        @if($volver)
            <a href="{{ $volver }}" class="btn btn-sm btn-volver mb-2"><i class="bi bi-arrow-left me-1"></i> {{ $volverTexto }}</a>
        @endif
        <h4 class="mb-1">{{ $titulo }}</h4>
        @if($subtitulo)<p class="text-muted mb-0">{{ $subtitulo }}</p>@endif
    </div>
    @if(trim($slot))
        <div class="page-head-acciones">{{ $slot }}</div>
    @endif
</div>
