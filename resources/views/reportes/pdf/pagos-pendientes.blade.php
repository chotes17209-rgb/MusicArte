@extends('layouts.pdf')
@section('titulo', 'Pagos pendientes')
@section('subtitulo', \App\Models\Pago::MESES[(int) $mes].' '.$anio.(request('estado') ? ' · Estado: '.(['pendiente' => 'Sin pagar', 'a_cuenta' => 'A cuenta', 'pagado' => 'Pagado'][request('estado')] ?? request('estado')) : ''))

@section('contenido')
@php $estados = ['pendiente' => 'Sin pagar', 'a_cuenta' => 'A cuenta', 'pagado' => 'Pagado']; @endphp
<table class="resumen"><tr>
    <td><div class="etq">Por cobrar</div><div class="val rojo">S/ {{ number_format($data->sum('saldo'), 2) }}</div><div class="det">saldo pendiente</div></td>
    <td><div class="etq">Pagos</div><div class="val">{{ $data->count() }}</div><div class="det">{{ $data->pluck('alumno_id')->unique()->count() }} alumnos</div></td>
    <td><div class="etq">Sin pagar</div><div class="val">{{ $data->where('estado', 'pendiente')->count() }}</div><div class="det">no han abonado nada</div></td>
    <td><div class="etq">A cuenta</div><div class="val ambar">{{ $data->where('estado', 'a_cuenta')->count() }}</div><div class="det">S/ {{ number_format($data->where('estado', 'a_cuenta')->sum(fn ($p) => $p->monto_total - $p->saldo), 2) }} ya abonado</div></td>
</tr></table>

<table class="tabla">
    <thead><tr><th>Alumno</th><th>Taller</th><th>Maestro</th><th>Estado</th><th class="der">Total</th><th class="der">Abonado</th><th class="der">Saldo</th></tr></thead>
    <tbody>
    @forelse($data as $p)
        <tr>
            <td class="fuerte">{{ $p->alumno->nombre ?? '—' }}</td>
            <td>{{ $p->alumnoTaller->especialidad->nombre ?? '—' }}</td>
            <td class="muted">{{ $p->alumnoTaller->maestro->nombre ?? '—' }}</td>
            <td><span class="estado {{ $p->estado }}">{{ $estados[$p->estado] ?? $p->estado }}</span></td>
            <td class="der">S/ {{ number_format($p->monto_total, 2) }}</td>
            <td class="der">S/ {{ number_format($p->monto_total - $p->saldo, 2) }}</td>
            <td class="der fuerte rojo">S/ {{ number_format($p->saldo, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="centro muted">No hay pagos pendientes. Todos al día.</td></tr>
    @endforelse
    </tbody>
    <tfoot><tr><td colspan="4">Total</td><td class="der">S/ {{ number_format($data->sum('monto_total'), 2) }}</td><td class="der">S/ {{ number_format($data->sum(fn ($p) => $p->monto_total - $p->saldo), 2) }}</td><td class="der">S/ {{ number_format($data->sum('saldo'), 2) }}</td></tr></tfoot>
</table>
@endsection
