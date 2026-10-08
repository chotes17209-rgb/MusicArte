@extends('layouts.app')
@section('titulo', 'Pago')

@section('contenido')
@php
    $taller = $pago->alumnoTaller;
    $abonado = $pago->monto_total - $pago->saldo;
    $tonoEstado = ['pagado' => 'verde', 'a_cuenta' => 'ambar', 'pendiente' => 'rojo'][$pago->estado] ?? null;
@endphp
<x-page-head :titulo="$pago->alumno->nombre ?? 'Pago'"
             :subtitulo="($pago->concepto ?: 'Mensualidad').' · '.$pago->mesLabel().' '.$pago->anio"
             :volver="route('pagos.index')" volver-texto="Pagos">
    <a href="{{ route('pagos.recibo', $pago) }}" target="_blank" class="btn btn-light" data-sin-ventana><i class="bi bi-file-earmark-pdf me-1"></i> Estado de cuenta</a>
</x-page-head>

<div class="stats">
    <x-stat label="Debe pagar" :valor="'S/ '.number_format($pago->monto_total, 2)" />
    <x-stat label="Ya pagó" :valor="'S/ '.number_format($abonado, 2)" tono="verde" />
    <x-stat label="Le falta" :valor="'S/ '.number_format($pago->saldo, 2)" :tono="$pago->saldo > 0 ? 'rojo' : 'verde'" />
    <x-stat label="Estado" :valor="$pago->estadoLabel()" :tono="$tonoEstado" />
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card p-3 h-100">
            <h6 class="seccion-titulo">Datos del pago</h6>
            <dl class="ficha mb-0">
                <dt>Alumno</dt><dd>{{ $pago->alumno->nombre ?? '—' }}</dd>
                @if($pago->esMatricula())
                    <dt>Concepto</dt><dd><span class="badge bg-primary"><i class="bi bi-award"></i> {{ $pago->tallerLabel() }}</span> <span class="text-muted small">(se paga una vez al año)</span></dd>
                @else
                <dt>Taller</dt><dd>{{ $taller->especialidad->nombre ?? '—' }}</dd>
                <dt>Maestro</dt><dd>{{ $taller->maestro->nombre ?? '—' }}</dd>
                @endif
                <dt>Periodo</dt><dd>{{ $taller->periodo->nombre ?? $pago->mesLabel().' '.$pago->anio }}</dd>
                @if($taller?->modalidadLabel())
                    <dt>Modalidad</dt><dd>{{ $taller->modalidadLabel() }}</dd>
                @endif
                <dt>Concepto</dt><dd>{{ $pago->concepto ?: 'Mensualidad' }}</dd>
                @if($taller?->nota_mensualidad)
                    <dt>Nota</dt><dd class="fst-italic">{{ $taller->nota_mensualidad }}</dd>
                @endif
                @if($pago->observacionVisible() && $pago->observacionVisible() !== $taller?->nota_mensualidad)
                    <dt>Observación</dt><dd>{{ $pago->observacionVisible() }}</dd>
                @endif
                <dt>Último pago</dt><dd>{{ optional($pago->fecha_pago)->format('d/m/Y') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-3 h-100">
            <h6 class="seccion-titulo">Abonos ({{ $pago->abonos->count() }})</h6>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Fecha</th><th>Método</th><th>N° recibo</th><th class="text-end">Monto</th><th class="text-end"></th></tr></thead>
                    <tbody>
                    @forelse($pago->abonos as $ab)
                        <tr>
                            <td>{{ $ab->fecha->format('d/m/Y') }}</td>
                            <td>{{ $ab->metodoLabel() }}</td>
                            <td>{{ $ab->recibo_nro ?? '—' }}</td>
                            <td class="text-end fw-semibold">S/ {{ number_format($ab->monto, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('pagos.abonos.recibo', $ab) }}" target="_blank" class="btn btn-sm btn-light btn-icon" title="Recibo" data-sin-ventana><i class="bi bi-file-earmark-pdf"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="vacio py-3"><i class="bi bi-cash-coin"></i>Aún no ha abonado nada.</div></td></tr>
                    @endforelse
                    </tbody>
                    @if($pago->abonos->isNotEmpty())
                        <tfoot><tr><td colspan="3" class="fw-semibold">Total abonado</td><td class="text-end fw-semibold">S/ {{ number_format($abonado, 2) }}</td><td></td></tr></tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
