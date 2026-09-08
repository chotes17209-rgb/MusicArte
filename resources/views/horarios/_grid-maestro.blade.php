@php
    // Dias a mostrar como columnas: Lunes a Sabado siempre; Domingo solo si
    // hay algun horario ese dia (igual que el cuadro fisico de referencia).
    $diasBase = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIÉRCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SÁBADO'];
    if ($horarios->contains('dia_semana', 7)) {
        $diasBase[7] = 'DOMINGO';
    }

    // Filas: todas las horas de inicio distintas usadas, ordenadas.
    $horas = $horarios->pluck('hora_inicio')->unique()->sort()->values();

    $fmtHora = function ($h) {
        return strtr(\Carbon\Carbon::parse($h)->format('g:i A'), ['AM' => 'a.m.', 'PM' => 'p.m.']);
    };

    $porCelda = $horarios->groupBy(fn ($h) => $h->hora_inicio.'|'.$h->dia_semana);
@endphp

<div class="tablero-maestro">
    <div class="tablero-header">
        <span class="tablero-titulo">MAESTRO(A) {{ Str::upper($maestro->nombre) }}</span>
        @if($maestro->especialidadesLabel() !== '—')
            <span class="tablero-sub">{{ Str::upper($maestro->especialidadesLabel()) }}</span>
        @endif
    </div>

    @if($horas->isEmpty())
        <div class="text-muted small p-3">Este maestro no tiene horarios programados en el periodo seleccionado.</div>
    @else
        <div class="table-responsive">
            <table class="table tablero-tabla mb-0">
                <thead>
                    <tr>
                        <th class="col-hora">HORA</th>
                        @foreach($diasBase as $num => $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($horas as $hora)
                    <tr>
                        <td class="col-hora">{{ $fmtHora($hora) }}</td>
                        @foreach($diasBase as $num => $label)
                            @php $celda = $porCelda->get($hora.'|'.$num, collect()); @endphp
                            <td class="{{ $celda->isNotEmpty() ? 'celda-ocupada' : '' }}">
                                @foreach($celda as $h)
                                    <div class="alumno-celda {{ !$h->activo ? 'inactivo' : '' }}">
                                        {{ $h->alumno->nombre ?? '—' }}@if($h->alumno && $h->alumno->edad !== null)<span class="edad-celda">({{ $h->alumno->edad }})</span>@endif
                                        @if(!$h->activo)<i class="bi bi-x-lg text-danger ms-1"></i>@endif
                                    </div>
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
