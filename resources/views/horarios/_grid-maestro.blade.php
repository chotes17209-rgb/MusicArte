@php
    // Cuadro de un maestro, como la hoja que se pega en el salon: solo los
    // dias y horas en que tiene clases; en cada celda, los alumnos con su edad.
    $nombresDias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
    $dias = $horarios->pluck('dia_semana')->unique()->sort()->values();
    $horas = $horarios->map(fn ($h) => substr($h->hora_inicio, 0, 5))->unique()->sort()->values();
    $porCelda = $horarios->groupBy(fn ($h) => substr($h->hora_inicio, 0, 5).'|'.$h->dia_semana);
    $variasEspecialidades = $horarios->pluck('especialidad_id')->unique()->count() > 1;
    $salon = $horarios->pluck('salon')->filter()->countBy()->sortDesc()->keys()->first();
    $especialidades = $horarios->pluck('especialidad.nombre')->filter()->unique()->implode(' y ');
@endphp

<div class="cuadro">
    <div class="cuadro-cab">
        <div class="cuadro-titulo">{{ $maestro->nombre }}</div>
        <div class="cuadro-sub">{{ $especialidades ?: $maestro->especialidadesLabel() }}@if($salon) · Salón {{ $salon }}@endif</div>
        <div class="cuadro-cuenta">{{ $horarios->pluck('alumno_id')->unique()->count() }} alumnos</div>
    </div>

    @if($horas->isEmpty())
        <div class="text-muted small p-3">Sin horarios en este periodo.</div>
    @else
        <div class="table-responsive">
            <table class="cuadro-tabla">
                <thead>
                    <tr>
                        <th class="cuadro-hora">Hora</th>
                        @foreach($dias as $d)<th>{{ $nombresDias[$d] }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($horas as $hora)
                    <tr>
                        <td class="cuadro-hora">{{ \Carbon\Carbon::createFromFormat('H:i', $hora)->format('g:i a') }}</td>
                        @foreach($dias as $d)
                            @php $celda = $porCelda->get($hora.'|'.$d, collect())->sortBy(fn ($h) => $h->alumno->nombre ?? ''); @endphp
                            <td class="{{ $celda->isEmpty() ? 'vacia' : '' }}">
                                @foreach($celda as $h)
                                    <a href="{{ route('alumnos.show', $h->alumno_id) }}" class="cuadro-alumno {{ !$h->activo ? 'inactivo' : '' }}" title="{{ $h->alumno->nombre ?? '' }} · {{ $h->especialidad->nombre ?? '' }} · {{ \Carbon\Carbon::parse($h->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($h->hora_fin)->format('H:i') }}">
                                        <span class="punto" style="background: {{ $h->especialidad->color ?? '#999' }}"></span>
                                        <span class="nombre">{{ $h->alumno ? $h->alumno->nombreCorto() : '—' }}</span>
                                        @if($h->alumno && $h->alumno->edadNumero())<span class="edad">({{ $h->alumno->edadNumero() }})</span>@endif
                                        @if($variasEspecialidades)<span class="inst">{{ mb_strtolower($h->especialidad->nombre ?? '') }}</span>@endif
                                    </a>
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
