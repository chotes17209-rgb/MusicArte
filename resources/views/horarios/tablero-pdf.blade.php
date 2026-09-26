@extends('layouts.pdf')
@section('titulo', 'Horarios por maestro')
@section('subtitulo', $periodo->nombre.' · '.$periodo->fecha_inicio->format('d/m').' al '.$periodo->fecha_fin->format('d/m/Y'))

@section('estilos')
    .cuadro { margin-bottom: 12px; page-break-inside: avoid; }
    .cuadro-cab { background: #3d2c8d; color: #fff; padding: 5px 8px; font-size: 10px; font-weight: bold; }
    .cuadro-cab span { font-weight: normal; font-size: 8.5px; color: #d9d4ee; }
    .cuadro table th { background: #f1eff8; color: #3d2c8d; font-size: 8px; padding: 4px; border: 1px solid #d9d4ee; text-align: center; }
    .cuadro table td { border: 1px solid #e3e1ec; padding: 3px 4px; vertical-align: top; font-size: 8.5px; }
    .cuadro .hora { width: 58px; text-align: center; font-weight: bold; background: #fafaf8; }
    .cuadro .ocupada { background: #fbfaff; }
    .al { margin: 1px 0; }
    .al .e { color: #8a8880; }
    .al .i { color: #8a8880; font-style: italic; }
@endsection

@section('contenido')
@php $nombresDias = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIÉRCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SÁBADO', 7 => 'DOMINGO']; @endphp
@forelse($maestros as $maestro)
    @php
        $horarios = $horariosPorMaestro->get($maestro->id, collect());
        $dias = $horarios->pluck('dia_semana')->unique()->sort()->values();
        $horas = $horarios->map(fn ($h) => substr($h->hora_inicio, 0, 5))->unique()->sort()->values();
        $porCelda = $horarios->groupBy(fn ($h) => substr($h->hora_inicio, 0, 5).'|'.$h->dia_semana);
        $varias = $horarios->pluck('especialidad_id')->unique()->count() > 1;
        $salon = $horarios->pluck('salon')->filter()->countBy()->sortDesc()->keys()->first();
    @endphp
    <div class="cuadro">
        <div class="cuadro-cab">MAESTRO(A) {{ mb_strtoupper($maestro->nombre) }} <span>— {{ mb_strtoupper($horarios->pluck('especialidad.nombre')->filter()->unique()->implode(' y ')) }}@if($salon) · SALÓN {{ $salon }}@endif</span></div>
        <table>
            <tr>
                <th class="hora">HORA</th>
                @foreach($dias as $d)<th>{{ $nombresDias[$d] }}</th>@endforeach
            </tr>
            @foreach($horas as $hora)
                <tr>
                    <td class="hora">{{ \Carbon\Carbon::createFromFormat('H:i', $hora)->format('g:i a') }}</td>
                    @foreach($dias as $d)
                        @php $celda = $porCelda->get($hora.'|'.$d, collect())->sortBy(fn ($h) => $h->alumno->nombre ?? ''); @endphp
                        <td class="{{ $celda->isNotEmpty() ? 'ocupada' : '' }}">
                            @foreach($celda as $h)
                                <div class="al">{{ mb_strtoupper($h->alumno ? $h->alumno->nombreCorto() : '—') }}@if($h->alumno && $h->alumno->edadNumero())<span class="e"> ({{ $h->alumno->edadNumero() }})</span>@endif @if($varias)<span class="i">{{ mb_strtolower($h->especialidad->nombre ?? '') }}</span>@endif</div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <p class="muted">No hay horarios en este periodo.</p>
@endforelse
@endsection
