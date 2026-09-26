@extends('layouts.app')
@section('titulo', 'Ingresos y egresos')

@section('contenido')
<x-page-head titulo="Ingresos y egresos {{ $anio }}"
             subtitulo="Lo cobrado a los alumnos frente a los gastos (egresos, caja chica y planilla de maestros), mes a mes."
             :volver="route('reportes.index')" volver-texto="Reportes">
    <form method="GET" data-autofiltro>
        <input type="number" name="anio" value="{{ $anio }}" class="form-control" style="width:96px">
    </form>
    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-light" data-sin-ventana><i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF</a>
</x-page-head>

@php
    $totalIn = $data->sum('ingresos'); $totalEg = $data->sum('egresos'); $balance = $totalIn - $totalEg;
    $max = max(1, $data->max('ingresos'), $data->max('egresos'));
@endphp
<div class="stats">
    <x-stat label="Cobrado en el año" :valor="'S/ '.number_format($totalIn, 2)" tono="verde" />
    <x-stat label="Gastos del año" :valor="'S/ '.number_format($totalEg, 2)" tono="rojo" />
    <x-stat label="Balance" :valor="'S/ '.number_format($balance, 2)" :tono="$balance >= 0 ? 'verde' : 'rojo'" />
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Mes</th><th class="text-end">Cobrado</th><th class="text-end">Gastos</th><th class="text-end">Balance</th><th style="min-width:220px">Comparación</th></tr></thead>
            <tbody>
            @foreach($data as $d)
                <tr class="{{ $d['ingresos'] == 0 && $d['egresos'] == 0 ? 'text-muted' : '' }}">
                    <td class="fw-semibold">{{ $d['mes'] }}</td>
                    <td class="text-end">S/ {{ number_format($d['ingresos'], 2) }}</td>
                    <td class="text-end">S/ {{ number_format($d['egresos'], 2) }}</td>
                    <td class="text-end fw-semibold {{ $d['balance'] > 0 ? 'tono-verde' : ($d['balance'] < 0 ? 'tono-rojo' : '') }}">S/ {{ number_format($d['balance'], 2) }}</td>
                    <td>
                        <div class="barra mb-1" title="Cobrado"><span class="tono-verde" style="width: {{ $d['ingresos'] / $max * 100 }}%"></span></div>
                        <div class="barra" title="Gastos"><span class="tono-rojo" style="width: {{ $d['egresos'] / $max * 100 }}%"></span></div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="text-end">S/ {{ number_format($totalIn, 2) }}</td>
                    <td class="text-end">S/ {{ number_format($totalEg, 2) }}</td>
                    <td class="text-end {{ $balance >= 0 ? 'tono-verde' : 'tono-rojo' }}">S/ {{ number_format($balance, 2) }}</td>
                    <td class="small text-muted fw-normal"><span class="tono-verde">▬</span> Cobrado &nbsp; <span class="tono-rojo">▬</span> Gastos</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
