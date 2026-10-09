@extends('layouts.app')
@section('titulo', 'Alumnos')

@push('estilos')
<style>
    .caja-matricula { border: 1px solid var(--borde); border-left: 3px solid var(--acento); border-radius: var(--radio-sm); background: var(--acento-suave); padding: .75rem .9rem; }
</style>
@endpush

@section('contenido')

{{-- ======================================================
     Pestanas de la seccion Alumnos. "Periodos" (con su CRUD y el
     pase de alumnos al siguiente periodo) vive ahora aqui adentro;
     todo lo demas de Alumnos sigue exactamente igual dentro de su
     propio tab-pane, sin cambios de logica.
     ====================================================== --}}
<ul class="nav nav-tabs mb-3" id="tabsAlumnos" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-btn-alumnos" data-bs-toggle="tab" data-bs-target="#tab-alumnos"
                type="button" role="tab" aria-controls="tab-alumnos">
            <i class="bi bi-people me-1"></i> Alumnos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-btn-periodos" data-bs-toggle="tab" data-bs-target="#tab-periodos"
                type="button" role="tab" aria-controls="tab-periodos">
            <i class="bi bi-calendar-range me-1"></i> Periodos
        </button>
    </li>
</ul>

<div class="tab-content" id="tabsAlumnosContent">
<div class="tab-pane fade" id="tab-alumnos" role="tabpanel" aria-labelledby="tab-btn-alumnos">

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Alumnos</h5>
        <small class="text-muted">Registro de estudiantes del centro cultural</small>
    </div>
    <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="nuevoAlumno()">
        <i class="bi bi-plus-lg me-1"></i> Nuevo Alumno
    </button>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" id="formFiltrosAlumnos" method="GET" onsubmit="return false;">
        <div class="col-md-3">
            <input type="text" name="buscar" id="filtro_buscar" class="form-control"
                   placeholder="Buscar por nombre, DNI o tutor..." value="{{ request('buscar') }}" autocomplete="off">
        </div>
        <div class="col-md-2">
            <select name="periodo_id" id="filtro_periodo_id" class="form-select">
                <option value="">Todos los periodos</option>
                {{-- Filtro: todos los periodos (tambien los cerrados) para poder ver alumnos de meses anteriores. --}}
                @foreach($todosPeriodos as $p)
                    <option value="{{ $p->id }}" @selected(request('periodo_id') == $p->id)>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="maestro_id" id="filtro_maestro_id" class="form-select">
                <option value="">Todos los maestros</option>
                @foreach($maestros as $m)
                    <option value="{{ $m->id }}" @selected(request('maestro_id') == $m->id)>{{ $m->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="especialidad_id" id="filtro_especialidad_id" class="form-select">
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $esp)
                    <option value="{{ $esp->id }}" @selected(request('especialidad_id') == $esp->id)>{{ $esp->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" id="filtro_estado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activo" @selected(request('estado')=='activo')>Activos</option>
                <option value="inactivo" @selected(request('estado')=='inactivo')>Inactivos</option>
            </select>
        </div>
        <div class="col-md-1 d-grid">
            <button type="button" class="btn btn-light" onclick="limpiarFiltrosAlumnos()" title="Limpiar filtros">
                <i class="bi bi-x-lg"></i><span class="d-md-none ms-1">Limpiar filtros</span>
            </button>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="imprimirListaAlumnos()">
                <i class="bi bi-printer me-1"></i> Imprimir lista de alumnos
            </button>
        </div>
    </form>
</div>

<div class="card p-3">
    <div id="tablaAlumnosWrap">
        @include('alumnos._tabla')
    </div>
</div>

<div class="modal fade" id="modalAlumno" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formAlumno">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalAlumno">Nuevo Alumno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="alumno_id">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label small fw-semibold">Nombre completo</label>
                        <input type="text" class="form-control" id="alumno_nombre" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small fw-semibold">Edad</label>
                        <input type="text" class="form-control" id="alumno_edad" readonly tabindex="-1"
                               placeholder="Se calcula sola">
                        <small class="text-muted">Se calcula automáticamente desde la fecha de nacimiento.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha de nacimiento</label>
                        <input type="date" class="form-control" id="alumno_fecha_nacimiento">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">DNI</label>
                        <input type="text" class="form-control" id="alumno_dni">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Tutor / Apoderado</label>
                        <input type="text" class="form-control" id="alumno_tutor">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Celular de contacto</label>
                        <input type="text" class="form-control" id="alumno_celular">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha de ingreso</label>
                        <input type="date" class="form-control" id="alumno_fecha_ingreso">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="alumno_activo" checked>
                            <label class="form-check-label small">Alumno activo</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Diagnóstico / condicion especial (opcional)</label>
                    <textarea class="form-control" id="alumno_diagnostico" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Observaciones</label>
                    <textarea class="form-control" id="alumno_observaciones" rows="2"></textarea>
                </div>

                {{-- ======================================================
                     BLOQUE A: solo se ve al crear un alumno nuevo. Permite
                     inscribirlo de una vez en su primer taller. Despues de
                     guardar, para agregar mas talleres se usa el bloque B.
                     ====================================================== --}}
                <div id="bloquePrimerTaller">
                    <hr>
                    <h6 class="fw-semibold small text-uppercase text-muted mb-1">Primer taller (opcional)</h6>
                    <small class="text-muted d-block mb-2">
                        Puedes inscribir al alumno en su primer taller ahora. Luego de guardar, podras
                        agregarle mas talleres desde el boton "Editar".
                    </small>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Taller (especialidad)</label>
                            <select class="form-select" id="alumno_especialidad_id">
                                <option value="">-- Selecciona --</option>
                                @foreach($especialidades as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Maestro</label>
                            <select class="form-select" id="alumno_maestro_id">
                                <option value="">-- Selecciona --</option>
                                @foreach($maestros as $m)
                                    <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-semibold">Periodo</label>
                            <select class="form-select" id="alumno_periodo_id">
                                <option value="">-- No programar clases ahora --</option>
                                @foreach($periodos as $p)
                                    <option value="{{ $p->id }}" data-anio="{{ $p->anio }}"
                                        data-inicio="{{ $p->fecha_inicio->format('d/m/Y') }}"
                                        data-fin="{{ $p->fecha_fin->format('d/m/Y') }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="alumno_periodo_duracion"></small>
                        </div>
                    </div>
                    @include('alumnos._mensualidad', ['prefijo' => 'alumno', 'tamano' => ''])
                    <div class="mb-1">
                        <label class="form-label small fw-semibold">Días y horario de clase</label>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr><th style="width:36px"></th><th>Día</th><th>Hora inicio</th><th>Hora fin</th></tr>
                                </thead>
                                <tbody>
                                    @foreach(['1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado','7'=>'Domingo'] as $num => $label)
                                    <tr>
                                        <td>
                                            <input class="form-check-input" type="checkbox" value="{{ $num }}"
                                                   id="alumno_dia_{{ $num }}" onchange="toggleDiaHorario('alumno', {{ $num }})">
                                        </td>
                                        <td><label for="alumno_dia_{{ $num }}" class="mb-0">{{ $label }}</label></td>
                                        <td><input type="time" class="form-control form-control-sm" id="alumno_dia_{{ $num }}_inicio" disabled></td>
                                        <td><input type="time" class="form-control form-control-sm" id="alumno_dia_{{ $num }}_fin" disabled></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Cada día puede tener una hora distinta. El salón se asigna desde el módulo de Horarios.</small>
                    </div>
                </div>

                {{-- ======================================================
                     BLOQUE B: solo se ve al editar un alumno existente.
                     Lista sus talleres actuales y permite agregar, editar
                     o quitar cada uno de forma independiente (seccion 3).
                     ====================================================== --}}
                <div id="bloqueTalleresExistente" class="d-none">
                    <hr>
                    <div class="caja-matricula mb-3" id="panelMatricula">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h6 class="fw-semibold small text-uppercase mb-0"><i class="bi bi-award me-1"></i> Matrícula <span id="matricula_anio_txt"></span></h6>
                                <div class="small" id="matricula_estado_txt"></div>
                            </div>
                            <a href="#" class="btn btn-sm btn-light d-none" id="matricula_ver_pago" target="_blank" title="El cobro (abonos y recibo) se registra en Pagos"><i class="bi bi-cash-stack me-1"></i> Cobrar en Pagos</a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold small text-uppercase text-muted mb-0">Talleres del alumno</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAgregarTaller" onclick="mostrarFormTaller()">
                            <i class="bi bi-plus-lg"></i> Agregar taller
                        </button>
                    </div>

                    <div id="panelListaTalleres">
                        <div id="listaTalleresAlumno" class="vstack gap-2"></div>
                    </div>

                    <div id="panelFormTaller" class="d-none border rounded p-3 bg-light">
                        <input type="hidden" id="taller_id">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-semibold">Taller (especialidad)</label>
                                <select class="form-select form-select-sm" id="taller_especialidad_id">
                                    <option value="">-- Selecciona --</option>
                                    @foreach($especialidades as $esp)
                                        <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-semibold">Maestro</label>
                                <select class="form-select form-select-sm" id="taller_maestro_id">
                                    <option value="">-- Selecciona --</option>
                                    @foreach($maestros as $m)
                                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label class="form-label small fw-semibold">Periodo</label>
                                <select class="form-select form-select-sm" id="taller_periodo_id">
                                    <option value="">-- Sin programar clases --</option>
                                    @foreach($periodos as $p)
                                        <option value="{{ $p->id }}" data-anio="{{ $p->anio }}"
                                            data-inicio="{{ $p->fecha_inicio->format('d/m/Y') }}"
                                            data-fin="{{ $p->fecha_fin->format('d/m/Y') }}">{{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="taller_periodo_duracion"></small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label small fw-semibold">Salón</label>
                                <input type="text" class="form-control form-control-sm" id="taller_salon">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small fw-semibold">Estado</label>
                                <select class="form-select form-select-sm" id="taller_estado">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        @include('alumnos._mensualidad', ['prefijo' => 'taller', 'tamano' => 'form-select-sm'])
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Días y horario de este taller</label>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr><th style="width:36px"></th><th>Día</th><th>Hora inicio</th><th>Hora fin</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['1'=>'Lunes','2'=>'Martes','3'=>'Miércoles','4'=>'Jueves','5'=>'Viernes','6'=>'Sábado','7'=>'Domingo'] as $num => $label)
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" value="{{ $num }}"
                                                       id="taller_dia_{{ $num }}" onchange="toggleDiaHorario('taller', {{ $num }})">
                                            </td>
                                            <td><label for="taller_dia_{{ $num }}" class="mb-0">{{ $label }}</label></td>
                                            <td><input type="time" class="form-control form-control-sm" id="taller_dia_{{ $num }}_inicio" disabled></td>
                                            <td><input type="time" class="form-control form-control-sm" id="taller_dia_{{ $num }}_fin" disabled></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">Deja los días sin marcar si no quieres (re)programar el calendario de este taller ahora.</small>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-light" onclick="mostrarListaTalleres()">Cancelar</button>
                            <button type="button" class="btn btn-sm btn-morado" id="btnGuardarTaller" onclick="guardarTaller()">Guardar taller</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>

</div>{{-- /#tab-alumnos --}}

<div class="tab-pane fade" id="tab-periodos" role="tabpanel" aria-labelledby="tab-btn-periodos">

{{-- ======================================================
     Seccion Periodos (movida aqui desde su propia pagina). CRUD
     completo de periodos + pase de alumnos activos al siguiente
     periodo. Misma logica de siempre (PeriodoController), solo
     cambio donde vive en la pantalla.
     ====================================================== --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">Periodos</h5>
        <small class="text-muted">Define cuanto dura cada periodo de clases (normalmente 4 semanas por mes)</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('alumnos.historial') }}" class="btn btn-ir">
            <i class="bi bi-clock-history me-1"></i> Ver historial por alumno
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPaseAlumnos" onclick="prepararPaseAlumnos()">
            <i class="bi bi-arrow-right-circle me-1"></i> Pasar alumnos al siguiente periodo
        </button>
        <button class="btn btn-morado" data-bs-toggle="modal" data-bs-target="#modalPeriodo" onclick="nuevoPeriodo()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Periodo
        </button>
    </div>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Periodo</th><th>Fechas</th><th>Duración</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            @forelse($todosPeriodos as $p)
                @php($enCurso = now()->startOfDay()->between($p->fecha_inicio, $p->fecha_fin))
                <tr class="{{ $enCurso ? 'fila-actual' : '' }}">
                    <td class="fw-semibold">
                        {{ $p->nombre }}
                        @if($enCurso)<span class="badge bg-primary ms-1">En curso</span>@endif
                    </td>
                    <td>{{ $p->fecha_inicio->format('d/m/Y') }} <span class="text-muted">al</span> {{ $p->fecha_fin->format('d/m/Y') }}</td>
                    <td>{{ $p->duracionSemanas() }} semanas</td>
                    <td>
                        @if($p->activo)
                            <span class="badge bg-success">Activo</span>
                            <div class="small text-muted">{{ $p->haTerminado() ? 'Ya terminó, pero se reabrió a mano' : 'Se puede inscribir alumnos' }}</div>
                        @elseif($p->finalizado())
                            <span class="badge bg-secondary">Finalizado</span>
                            <div class="small text-muted">{{ $p->cerrado_en ? 'Se cerró el '.$p->cerrado_en->format('d/m/Y') : 'Solo historial' }}</div>
                        @else
                            <span class="badge bg-secondary">Cerrado</span>
                            <div class="small text-muted">Solo historial</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('periodos.show', $p) }}" class="btn btn-sm btn-light btn-icon" title="Ver"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-light btn-icon" onclick="editarPeriodo({{ $p->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-light btn-icon text-danger" onclick="eliminarPeriodo({{ $p->id }}, '{{ $p->nombre }}')"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay periodos creados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formPeriodo">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalPeriodo">Nuevo Periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="periodo_id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Mes</label>
                        <select class="form-select" id="periodo_mes" required>
                            <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option>
                            <option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option>
                            <option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option>
                            <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Año</label>
                        <input type="number" class="form-control" id="periodo_anio" value="{{ date('Y') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha inicio</label>
                        <input type="date" class="form-control" id="periodo_fecha_inicio">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Fecha fin</label>
                        <input type="date" class="form-control" id="periodo_fecha_fin">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light mb-3" onclick="calcularRangoAutomatico()">
                    <i class="bi bi-magic me-1"></i> Calcular 4 semanas automáticamente
                </button>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="periodo_activo" checked>
                    <label class="form-check-label small">Periodo activo</label>
                </div>
                <div class="alert alert-secondary small mt-3 mb-0">Si dejas las fechas vacías, se calculan automáticamente 4 semanas desde el día 1 del mes.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-morado">Guardar</button>
            </div>
        </form>
    </div>
</div>

{{-- ======================================================
     Seccion 8: pasar alumnos activos de un periodo al siguiente.
     ====================================================== --}}
<div class="modal fade" id="modalPaseAlumnos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pasar alumnos al siguiente periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Periodo anterior</label>
                        <select class="form-select" id="pase_periodo_anterior_id" onchange="cargarCandidatosPase()">
                            <option value="">-- Selecciona --</option>
                            @foreach($todosPeriodos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Periodo nuevo (destino)</label>
                        <select class="form-select" id="pase_periodo_nuevo_id">
                            <option value="">-- Selecciona --</option>
                            @foreach($todosPeriodos as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="pase_estado_vacio" class="text-muted small">Selecciona el periodo anterior para ver los alumnos que estuvieron activos.</div>

                <div id="pase_lista_wrap" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted">Alumnos activos en el periodo anterior. Desmarca a quienes no continuan.</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-light" onclick="marcarTodosPase(true)">Marcar todos</button>
                            <button type="button" class="btn btn-sm btn-light" onclick="marcarTodosPase(false)">Desmarcar todos</button>
                        </div>
                    </div>
                    <div id="pase_lista_alumnos" class="border rounded p-2" style="max-height:320px; overflow-y:auto;"></div>
                </div>

                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" id="pase_copiar_talleres" checked>
                    <label class="form-check-label" for="pase_copiar_talleres">
                        <span class="fw-semibold">Copiar sus talleres al nuevo periodo</span>
                        <span class="d-block small text-muted">Mismo maestro, días y horas, modalidad y mensualidad. Se crean sus clases y su mensualidad pendiente del nuevo mes. Si alguien cambia, se edita solo a ese alumno.</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-morado" onclick="confirmarPaseAlumnos()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Pasar seleccionados al nuevo periodo
                </button>
            </div>
        </div>
    </div>
</div>

</div>{{-- /#tab-periodos --}}
</div>{{-- /.tab-content --}}
@endsection

@push('scripts')
<script>
    const modalAlumno = new bootstrap.Modal('#modalAlumno');
    let talleresAlumnoActual = [];
    const HOY = @json(now()->toDateString());

    /* ---------------------------------------------------------------
     * 2.1 Edad automatica
     * ------------------------------------------------------------- */
    function calcularEdadDesdeFecha(fechaTexto) {
        if (!fechaTexto) return '';
        const nacimiento = new Date(fechaTexto + 'T00:00:00');
        if (isNaN(nacimiento.getTime())) return '';

        const hoy = new Date();
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const aunNoCumple = (hoy.getMonth() < nacimiento.getMonth()) ||
            (hoy.getMonth() === nacimiento.getMonth() && hoy.getDate() < nacimiento.getDate());
        if (aunNoCumple) edad--;

        return edad >= 0 ? edad : '';
    }

    document.getElementById('alumno_fecha_nacimiento').addEventListener('change', function () {
        document.getElementById('alumno_edad').value = calcularEdadDesdeFecha(this.value);
    });

    /* ---------------------------------------------------------------
     * Helper generico de dias/horas, usado por el bloque "primer
     * taller" (prefijo alumno_) y por el formulario de talleres
     * (prefijo taller_).
     * ------------------------------------------------------------- */
    function toggleDiaHorario(prefix, diaNum) {
        const checked = document.getElementById(prefix + '_dia_' + diaNum).checked;
        const inicio = document.getElementById(prefix + '_dia_' + diaNum + '_inicio');
        const fin = document.getElementById(prefix + '_dia_' + diaNum + '_fin');
        inicio.disabled = !checked;
        fin.disabled = !checked;
        if (!checked) {
            inicio.value = '';
            fin.value = '';
        }
    }

    /* ---------------------------------------------------------------
     * Modalidad y mensualidad del taller. La mensualidad se escribe a
     * mano; la modalidad se sugiere al marcar los dias de clase.
     * ------------------------------------------------------------- */
    function reiniciarMensualidad(prefijo, veces = '', monto = '', nota = '') {
        document.getElementById(prefijo + '_nota_mensualidad').value = nota ?? '';
        document.getElementById(prefijo + '_veces_semana').value = veces ?? '';
        document.getElementById(prefijo + '_monto_mensual').value = monto === null || monto === '' ? '' : Number(monto).toFixed(2);
    }

    ['alumno', 'taller'].forEach(prefijo => {
        for (let d = 1; d <= 7; d++) {
            document.getElementById(`${prefijo}_dia_${d}`).addEventListener('change', () => {
                const marcados = [...Array(7).keys()].filter(i => document.getElementById(`${prefijo}_dia_${i + 1}`).checked).length;
                if (marcados >= 1 && marcados <= 5) document.getElementById(prefijo + '_veces_semana').value = marcados;
            });
        }
    });

    /** Revisa que haya mensualidad cuando el taller tiene periodo (sin ella no se puede crear su pago). */
    function mensualidadValida(prefijo, periodoId) {
        if (!periodoId || document.getElementById(prefijo + '_monto_mensual').value !== '') return true;
        Swal.fire({ icon: 'warning', title: 'Falta la mensualidad', text: 'Escribe cuánto pagará al mes por este taller según la modalidad que eligió.' });
        document.getElementById(prefijo + '_monto_mensual').focus();
        return false;
    }

    function datosMensualidad(prefijo) {
        const veces = document.getElementById(prefijo + '_veces_semana').value;
        const monto = document.getElementById(prefijo + '_monto_mensual').value;
        const nota = document.getElementById(prefijo + '_nota_mensualidad').value.trim();
        return { veces_semana: veces || null, monto_mensual: monto === '' ? null : monto, nota_mensualidad: nota || null };
    }

    /* ---------------------------------------------------------------
     * Matricula (una vez por año, aparte de la mensualidad).
     * ------------------------------------------------------------- */
    let matriculaActual = { anio: null, pago: null };

    function textoMatricula(pago, anio) {
        if (!pago) return `Sin matrícula registrada en ${anio}.`;
        const total = Number(pago.monto_total), falta = Number(pago.saldo), pagado = total - falta;
        if (falta <= 0) return `S/ ${total.toFixed(2)} · <span class="badge bg-success">Pagada</span>`;
        if (pagado > 0) return `S/ ${total.toFixed(2)} · <span class="badge bg-warning">A cuenta</span> falta S/ ${falta.toFixed(2)}`;
        return `S/ ${total.toFixed(2)} · <span class="badge bg-danger">Pendiente de pago</span>`;
    }

    // Estado de la matricula del año (arriba de los talleres). Se registra o
    // cambia en el formulario del taller, al costado de la mensualidad.
    function renderMatricula(anio, pago) {
        matriculaActual = { anio, pago };
        document.getElementById('matricula_anio_txt').textContent = anio;
        const enlace = document.getElementById('matricula_ver_pago');
        enlace.classList.toggle('d-none', !pago);
        if (pago) enlace.href = `/pagos/${pago.id}`;
        document.getElementById('matricula_estado_txt').innerHTML = pago
            ? textoMatricula(pago, anio) + (pago.observacion ? ` <span class="text-muted fst-italic">· ${maEscapar(pago.observacion)}</span>` : '')
            : `<span class="text-muted">Sin matrícula registrada en ${anio}. Se registra al costado de la mensualidad, en el taller.</span>`;
    }

    /** Año de la matricula en el formulario del taller: el del periodo elegido. */
    function anioMatriculaTaller() {
        const opt = document.getElementById('taller_periodo_id').selectedOptions[0];
        return Number(opt?.dataset.anio || matriculaActual.anio || new Date().getFullYear());
    }

    function prepararMatriculaTaller() {
        const anio = anioMatriculaTaller();
        const pago = anio === Number(matriculaActual.anio) ? matriculaActual.pago : null;
        document.getElementById('taller_matricula_anio').textContent = anio;
        document.getElementById('taller_matricula_monto').value = pago ? Number(pago.monto_total).toFixed(2) : '';
        document.getElementById('taller_matricula_nota').value = pago?.observacion ?? '';
        document.getElementById('taller_matricula_estado').innerHTML = pago
            ? textoMatricula(pago, anio) + ' — se cobra en Pagos.'
            : 'Déjala vacía si no corresponde o ya la registraste.';
    }

    /** Guarda la matricula escrita en el formulario del taller (si cambio). */
    async function guardarMatriculaDelTaller(alumnoId) {
        const monto = document.getElementById('taller_matricula_monto').value;
        if (monto === '') return;
        const anio = anioMatriculaTaller();
        const nota = document.getElementById('taller_matricula_nota').value.trim() || null;
        const actual = anio === Number(matriculaActual.anio) ? matriculaActual.pago : null;
        if (actual && Number(actual.monto_total) === Number(monto) && (actual.observacion ?? null) === nota) return;

        const res = await maFetch(`/alumnos/${alumnoId}/matricula`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ anio, monto, nota }),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            if (anio === Number(matriculaActual.anio)) renderMatricula(anio, res.data);
        }
    }

    document.getElementById('taller_periodo_id').addEventListener('change', prepararMatriculaTaller);

    document.getElementById('alumno_periodo_id').addEventListener('change', function () {
        const anio = this.selectedOptions[0]?.dataset.anio;
        document.getElementById('alumno_matricula_anio').textContent = anio || @json($anioMatricula);
    });

    function diaCorto(n) {
        return ({1: 'Lun', 2: 'Mar', 3: 'Mie', 4: 'Jue', 5: 'Vie', 6: 'Sab', 7: 'Dom'})[n] || '';
    }

    function nuevoAlumno() {
        document.getElementById('formAlumno').reset();
        document.getElementById('alumno_id').value = '';
        document.getElementById('alumno_edad').value = '';
        document.getElementById('alumno_periodo_duracion').innerText = '';
        for (let diaNum = 1; diaNum <= 7; diaNum++) {
            document.getElementById('alumno_dia_' + diaNum + '_inicio').disabled = true;
            document.getElementById('alumno_dia_' + diaNum + '_inicio').value = '';
            document.getElementById('alumno_dia_' + diaNum + '_fin').disabled = true;
            document.getElementById('alumno_dia_' + diaNum + '_fin').value = '';
        }
        reiniciarMensualidad('alumno');
        document.getElementById('alumno_matricula_anio').textContent = @json($anioMatricula);
        document.getElementById('bloquePrimerTaller').classList.remove('d-none');
        document.getElementById('bloqueTalleresExistente').classList.add('d-none');
        document.getElementById('tituloModalAlumno').innerText = 'Nuevo Alumno';
    }

    document.getElementById('alumno_periodo_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const txt = document.getElementById('alumno_periodo_duracion');
        txt.innerText = this.value ? `Del ${opt.dataset.inicio} al ${opt.dataset.fin}` : '';
    });

    document.getElementById('taller_periodo_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const txt = document.getElementById('taller_periodo_duracion');
        txt.innerText = this.value ? `Del ${opt.dataset.inicio} al ${opt.dataset.fin}` : '';
    });

    /* ---------------------------------------------------------------
     * Seccion 3 y 4: gestion de multiples talleres por alumno.
     * ------------------------------------------------------------- */
    function renderListaTalleres(talleres) {
        // Del periodo mas reciente al mas antiguo: lo vigente queda arriba.
        talleresAlumnoActual = (talleres || []).slice().sort((a, b) =>
            (b.periodo?.fecha_inicio ?? '9999').localeCompare(a.periodo?.fecha_inicio ?? '9999') || b.id - a.id);
        const cont = document.getElementById('listaTalleresAlumno');

        if (!talleresAlumnoActual.length) {
            cont.innerHTML = '<div class="text-muted small">Este alumno todavía no tiene talleres. Usa "Agregar taller".</div>';
            return;
        }

        // Inicio del periodo mas reciente donde tiene un taller activo: lo anterior ya es historial.
        const inicioActual = talleresAlumnoActual
            .filter(t => t.estado === 'activo' && t.periodo)
            .map(t => (t.periodo.fecha_inicio ?? '').substring(0, 10))
            .sort().pop() ?? '';

        cont.innerHTML = talleresAlumnoActual.map(t => {
            const dias = (t.horarios || [])
                .map(h => `${diaCorto(h.dia_semana)} ${h.hora_inicio.substring(0, 5)}-${h.hora_fin.substring(0, 5)}`)
                .join(' · ') || 'Sin horario asignado';
            // Un taller de un periodo que ya termino es historial: se muestra "Finalizado".
            const terminado = t.periodo && ((t.periodo.fecha_fin ?? '').substring(0, 10) < HOY
                || (!t.periodo.activo && t.periodo.cerrado_en)
                || (t.periodo.fecha_inicio ?? '').substring(0, 10) < inicioActual);
            const estadoBadge = terminado
                ? '<span class="badge bg-secondary">Finalizado</span>'
                : (t.estado === 'activo'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>');

            return `
            <div class="border rounded p-2 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">${t.especialidad?.nombre ?? 'Taller'} ${estadoBadge}</div>
                    <div class="small text-muted">Maestro: ${t.maestro?.nombre ?? 'Sin asignar'} · Periodo: ${t.periodo?.nombre ?? 'Sin periodo'}</div>
                    <div class="small text-muted">${dias}</div>
                    <div class="small mt-1">${t.monto_mensual !== null && t.monto_mensual !== undefined
                        ? `<span class="fw-semibold">S/ ${Number(t.monto_mensual).toFixed(2)} al mes</span>${t.veces_semana ? ` · ${t.veces_semana === 1 ? '1 vez' : t.veces_semana + ' veces'} por semana` : ''}`
                        : '<span class="text-danger">Sin mensualidad: edítalo para asignarla</span>'}</div>
                    ${t.nota_mensualidad ? `<div class="small text-muted fst-italic"><i class="bi bi-sticky me-1"></i>${maEscapar(t.nota_mensualidad)}</div>` : ''}
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-light btn-icon" onclick="mostrarFormTaller(${t.id})"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-light btn-icon text-danger" onclick="quitarTaller(${t.id})"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
        }).join('');
    }

    function mostrarListaTalleres() {
        document.getElementById('panelListaTalleres').classList.remove('d-none');
        document.getElementById('panelFormTaller').classList.add('d-none');
        document.getElementById('btnAgregarTaller').classList.remove('d-none');
    }

    function mostrarFormTaller(tallerId = null) {
        document.getElementById('panelListaTalleres').classList.add('d-none');
        document.getElementById('panelFormTaller').classList.remove('d-none');
        document.getElementById('btnAgregarTaller').classList.add('d-none');

        document.getElementById('taller_id').value = '';
        document.getElementById('taller_especialidad_id').value = '';
        document.getElementById('taller_maestro_id').value = '';
        document.querySelectorAll('#taller_periodo_id option[data-temporal]').forEach(o => o.remove());
        document.getElementById('taller_periodo_id').value = '';
        document.getElementById('taller_periodo_duracion').innerText = '';
        document.getElementById('taller_salon').value = '';
        document.getElementById('taller_estado').value = 'activo';
        for (let d = 1; d <= 7; d++) {
            document.getElementById('taller_dia_' + d).checked = false;
            document.getElementById('taller_dia_' + d + '_inicio').disabled = true;
            document.getElementById('taller_dia_' + d + '_inicio').value = '';
            document.getElementById('taller_dia_' + d + '_fin').disabled = true;
            document.getElementById('taller_dia_' + d + '_fin').value = '';
        }
        reiniciarMensualidad('taller');
        prepararMatriculaTaller();

        if (!tallerId) return;

        const t = talleresAlumnoActual.find(x => x.id === tallerId);
        if (!t) return;

        document.getElementById('taller_id').value = t.id;
        document.getElementById('taller_especialidad_id').value = t.especialidad_id ?? '';
        document.getElementById('taller_maestro_id').value = t.maestro_id ?? '';
        document.getElementById('taller_salon').value = t.salon ?? '';
        document.getElementById('taller_estado').value = t.estado ?? 'activo';
        if (t.periodo_id) {
            const sel = document.getElementById('taller_periodo_id');
            // Un periodo ya finalizado no esta en la lista: se agrega para no perderlo al guardar.
            if (!sel.querySelector(`option[value="${t.periodo_id}"]`) && t.periodo) {
                const fecha = f => (f ?? '').substring(0, 10).split('-').reverse().join('/');
                const opt = new Option(`${t.periodo.nombre} (finalizado)`, t.periodo_id);
                opt.dataset.inicio = fecha(t.periodo.fecha_inicio);
                opt.dataset.fin = fecha(t.periodo.fecha_fin);
                opt.dataset.temporal = '1';
                sel.appendChild(opt);
            }
            sel.value = t.periodo_id;
            sel.dispatchEvent(new Event('change'));
        }
        (t.horarios || []).forEach(h => {
            const cb = document.getElementById('taller_dia_' + h.dia_semana);
            const hi = document.getElementById('taller_dia_' + h.dia_semana + '_inicio');
            const hf = document.getElementById('taller_dia_' + h.dia_semana + '_fin');
            if (cb) cb.checked = true;
            if (hi) { hi.disabled = false; hi.value = h.hora_inicio.substring(0, 5); }
            if (hf) { hf.disabled = false; hf.value = h.hora_fin.substring(0, 5); }
        });
        reiniciarMensualidad('taller', t.veces_semana, t.monto_mensual, t.nota_mensualidad);
        prepararMatriculaTaller();
    }

    async function guardarTaller() {
        const alumnoId = document.getElementById('alumno_id').value;
        if (!alumnoId) return;

        if (!document.getElementById('taller_especialidad_id').value) {
            Swal.fire({ icon: 'warning', title: 'Falta el taller', text: 'Selecciona la especialidad del taller.' });
            return;
        }

        const horarios = [];
        for (let d = 1; d <= 7; d++) {
            const cb = document.getElementById('taller_dia_' + d);
            if (cb && cb.checked) {
                horarios.push({
                    dia_semana: d,
                    hora_inicio: document.getElementById('taller_dia_' + d + '_inicio').value,
                    hora_fin: document.getElementById('taller_dia_' + d + '_fin').value,
                });
            }
        }
        if (horarios.length && !document.getElementById('taller_periodo_id').value) {
            Swal.fire({ icon: 'warning', title: 'Falta el periodo', text: 'Marcaste dias de clase pero no seleccionaste un periodo para este taller.' });
            return;
        }
        if (horarios.some(h => !h.hora_inicio || !h.hora_fin)) {
            Swal.fire({ icon: 'warning', title: 'Horario incompleto', text: 'Completa la hora de inicio y fin en cada dia marcado.' });
            return;
        }

        if (!mensualidadValida('taller', document.getElementById('taller_periodo_id').value)) return;

        const tallerId = document.getElementById('taller_id').value;
        const payload = {
            especialidad_id: document.getElementById('taller_especialidad_id').value,
            maestro_id: document.getElementById('taller_maestro_id').value || null,
            periodo_id: document.getElementById('taller_periodo_id').value || null,
            salon: document.getElementById('taller_salon').value,
            estado: document.getElementById('taller_estado').value,
            ...datosMensualidad('taller'),
        };
        if (horarios.length) payload.horarios = horarios;

        // Evita guardar dos veces si se presiona el boton varias veces (creaba talleres duplicados).
        const boton = document.getElementById('btnGuardarTaller');
        if (boton.disabled) return;
        boton.disabled = true;
        boton.textContent = 'Guardando…';

        try {
            const url = tallerId ? `/alumnos/talleres/${tallerId}` : `/alumnos/${alumnoId}/talleres`;
            const res = await maFetch(url, {
                method: tallerId ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res && res.ok) {
                maToast('success', res.message);
                await guardarMatriculaDelTaller(alumnoId);
                await recargarTalleresDelAlumno(alumnoId);
                mostrarListaTalleres();
            }
        } finally {
            boton.disabled = false;
            boton.textContent = 'Guardar taller';
        }
    }

    async function quitarTaller(tallerId) {
        if (!(await maConfirmarEliminar('este taller del alumno'))) return;
        const res = await maFetch(`/alumnos/talleres/${tallerId}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            const alumnoId = document.getElementById('alumno_id').value;
            await recargarTalleresDelAlumno(alumnoId);
        }
    }

    async function recargarTalleresDelAlumno(alumnoId) {
        const res = await maFetch(`/alumnos/${alumnoId}/edit`);
        if (res && res.ok) {
            renderListaTalleres(res.data.talleres);
        }
    }

    async function editarAlumno(id) {
        const res = await maFetch(`/alumnos/${id}/edit`);
        if (!res) return;
        const d = res.data;

        document.getElementById('alumno_id').value = d.id;
        document.getElementById('alumno_nombre').value = d.nombre;
        document.getElementById('alumno_fecha_nacimiento').value = (d.fecha_nacimiento ?? '').substring(0, 10);
        document.getElementById('alumno_edad').value = d.fecha_nacimiento ? calcularEdadDesdeFecha(d.fecha_nacimiento) : (d.edad ?? '');
        document.getElementById('alumno_dni').value = d.dni ?? '';
        document.getElementById('alumno_tutor').value = d.tutor ?? '';
        document.getElementById('alumno_celular').value = d.celular ?? '';
        document.getElementById('alumno_fecha_ingreso').value = (d.fecha_ingreso ?? '').substring(0, 10);
        document.getElementById('alumno_activo').checked = !!d.activo;
        document.getElementById('alumno_diagnostico').value = d.diagnostico ?? '';
        document.getElementById('alumno_observaciones').value = d.observaciones ?? '';

        document.getElementById('bloquePrimerTaller').classList.add('d-none');
        document.getElementById('bloqueTalleresExistente').classList.remove('d-none');
        renderListaTalleres(d.talleres);
        mostrarListaTalleres();
        renderMatricula(d.matricula_anio, d.matricula);

        document.getElementById('tituloModalAlumno').innerText = 'Editar Alumno';
        modalAlumno.show();
    }

    let guardandoAlumno = false;
    document.getElementById('formAlumno').addEventListener('submit', async (e) => {
        e.preventDefault();
        // Evita registrar el mismo alumno dos veces si se presiona Guardar varias veces.
        if (guardandoAlumno) return;
        guardandoAlumno = true;
        try { await guardarAlumno(); } finally { guardandoAlumno = false; }
    });

    async function guardarAlumno() {
        const id = document.getElementById('alumno_id').value;

        const payload = {
            nombre: document.getElementById('alumno_nombre').value,
            fecha_nacimiento: document.getElementById('alumno_fecha_nacimiento').value || null,
            dni: document.getElementById('alumno_dni').value,
            tutor: document.getElementById('alumno_tutor').value,
            celular: document.getElementById('alumno_celular').value,
            fecha_ingreso: document.getElementById('alumno_fecha_ingreso').value || null,
            activo: document.getElementById('alumno_activo').checked ? 1 : 0,
            diagnostico: document.getElementById('alumno_diagnostico').value,
            observaciones: document.getElementById('alumno_observaciones').value,
        };

        // El "primer taller" y la matricula solo aplican al crear un alumno nuevo.
        if (!id && document.getElementById('alumno_matricula_monto').value !== '') {
            payload.matricula_monto = document.getElementById('alumno_matricula_monto').value;
            payload.matricula_nota = document.getElementById('alumno_matricula_nota').value || null;
            payload.matricula_anio = document.getElementById('alumno_matricula_anio').textContent.trim();
        }
        if (!id) {
            const periodoId = document.getElementById('alumno_periodo_id').value;
            const horariosDias = [];
            for (let dia = 1; dia <= 7; dia++) {
                const cb = document.getElementById('alumno_dia_' + dia);
                if (cb && cb.checked) {
                    horariosDias.push({
                        dia_semana: dia,
                        hora_inicio: document.getElementById('alumno_dia_' + dia + '_inicio').value,
                        hora_fin: document.getElementById('alumno_dia_' + dia + '_fin').value,
                    });
                }
            }

            if (horariosDias.length && !periodoId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Falta el periodo',
                    text: 'Marcaste dias de clase pero no seleccionaste un Periodo.',
                });
                return;
            }
            if (horariosDias.some(h => !h.hora_inicio || !h.hora_fin)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Horario incompleto',
                    text: 'Completa la hora de inicio y de fin en cada dia que marcaste.',
                });
                return;
            }

            if (document.getElementById('alumno_especialidad_id').value && !mensualidadValida('alumno', periodoId)) return;
            if (document.getElementById('alumno_especialidad_id').value) {
                payload.especialidad_id = document.getElementById('alumno_especialidad_id').value;
                payload.maestro_id = document.getElementById('alumno_maestro_id').value || null;
                Object.assign(payload, datosMensualidad('alumno'));
                if (periodoId) payload.periodo_id = periodoId;
            }
            if (periodoId && horariosDias.length) {
                payload.periodo_id = periodoId;
                payload.horarios = horariosDias;
            }
        }

        const url = id ? `/alumnos/${id}` : '/alumnos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalAlumno.hide();
            buscarAlumnosReactivo();
        }
    }

    async function eliminarAlumno(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/alumnos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            buscarAlumnosReactivo();
        }
    }

    /* ---------------------------------------------------------------
     * 1.1 / 1.2 / 1.3 Filtros y busqueda reactiva (Fase 1, sin cambios).
     * ------------------------------------------------------------- */
    let debounceBuscarAlumnos = null;
    let busquedaAlumnosEnCurso = null;

    async function buscarAlumnosReactivo(url = null) {
        const form = document.getElementById('formFiltrosAlumnos');
        const params = new URLSearchParams(new FormData(form));
        const target = url || (`{{ route('alumnos.index') }}?` + params.toString());

        // Si llega una respuesta vieja (ej. de "ale") despues de la nueva
        // (ej. de "aless"), pisaba los resultados: se cancela la anterior.
        if (busquedaAlumnosEnCurso) busquedaAlumnosEnCurso.abort();
        const controller = new AbortController();
        busquedaAlumnosEnCurso = controller;

        let res;
        try {
            res = await fetch(target, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: controller.signal,
            });
        } catch (e) {
            return;
        }
        if (!res.ok || busquedaAlumnosEnCurso !== controller) return;
        const data = await res.json();
        if (busquedaAlumnosEnCurso !== controller) return;
        document.getElementById('tablaAlumnosWrap').innerHTML = data.html;

        window.history.replaceState({}, '', `{{ route('alumnos.index') }}?` + params.toString());
    }

    document.getElementById('filtro_buscar').addEventListener('input', function () {
        clearTimeout(debounceBuscarAlumnos);
        debounceBuscarAlumnos = setTimeout(() => buscarAlumnosReactivo(), 350);
    });

    ['filtro_periodo_id', 'filtro_maestro_id', 'filtro_especialidad_id', 'filtro_estado'].forEach(id => {
        document.getElementById(id).addEventListener('change', () => buscarAlumnosReactivo());
    });

    function limpiarFiltrosAlumnos() {
        document.getElementById('formFiltrosAlumnos').reset();
        buscarAlumnosReactivo();
    }

    /* 18. Imprimir lista de alumnos respetando los filtros actuales. */
    function imprimirListaAlumnos() {
        const form = document.getElementById('formFiltrosAlumnos');
        const params = new URLSearchParams(new FormData(form));
        window.open(`{{ route('alumnos.imprimir') }}?` + params.toString(), '_blank');
    }

    document.getElementById('tablaAlumnosWrap').addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link || !link.href) return;
        if (!link.closest('.pagination') && !link.closest('nav')) return;
        e.preventDefault();
        buscarAlumnosReactivo(link.href);
    });

    // Si llegamos desde "Ver perfil" con ?editar=ID (boton Editar del
    // perfil), abrimos el modal de edicion automaticamente.
    (function () {
        const params = new URLSearchParams(window.location.search);
        const editarId = params.get('editar');
        if (editarId) {
            editarAlumno(Number(editarId));
            const url = new URL(window.location.href);
            url.searchParams.delete('editar');
            window.history.replaceState({}, '', url);
        }
    })();

    /* ---------------------------------------------------------------
     * Pestanas Alumnos / Periodos: por defecto se abre "Alumnos"; si
     * se llega con ?tab=periodos o #periodos (ej. desde un enlace
     * antiguo a /periodos), se abre directamente "Periodos".
     * ------------------------------------------------------------- */
    (function () {
        const params = new URLSearchParams(window.location.search);
        const quierePeriodos = params.get('tab') === 'periodos' || window.location.hash === '#periodos';
        const idTab = quierePeriodos ? 'tab-btn-periodos' : 'tab-btn-alumnos';
        new bootstrap.Tab(document.getElementById(idTab)).show();
    })();
</script>
@endpush

@push('scripts')
<script>
    /* ===================================================================
     * Seccion Periodos (movida aqui desde su propia pagina). Misma logica
     * de siempre: CRUD de periodos + pase de alumnos activos al siguiente
     * periodo (PeriodoController@candidatos / pasarAlumnos).
     * =================================================================== */
    const modalPeriodo = new bootstrap.Modal('#modalPeriodo');

    function nuevoPeriodo() {
        document.getElementById('formPeriodo').reset();
        document.getElementById('periodo_id').value = '';
        document.getElementById('periodo_anio').value = new Date().getFullYear();
        document.getElementById('tituloModalPeriodo').innerText = 'Nuevo Periodo';
    }

    function calcularRangoAutomatico() {
        const mes = parseInt(document.getElementById('periodo_mes').value);
        const anio = parseInt(document.getElementById('periodo_anio').value);
        const inicio = new Date(anio, mes - 1, 1);
        const fin = new Date(anio, mes - 1, 1 + 27);
        const fmt = (d) => d.toISOString().substring(0, 10);
        document.getElementById('periodo_fecha_inicio').value = fmt(inicio);
        document.getElementById('periodo_fecha_fin').value = fmt(fin);
    }

    async function editarPeriodo(id) {
        const res = await maFetch(`/periodos/${id}/edit`);
        if (!res) return;
        const d = res.data;
        document.getElementById('periodo_id').value = d.id;
        document.getElementById('periodo_mes').value = d.mes;
        document.getElementById('periodo_anio').value = d.anio;
        document.getElementById('periodo_fecha_inicio').value = d.fecha_inicio;
        document.getElementById('periodo_fecha_fin').value = d.fecha_fin;
        document.getElementById('periodo_activo').checked = !!d.activo;
        document.getElementById('tituloModalPeriodo').innerText = 'Editar Periodo';
        modalPeriodo.show();
    }

    document.getElementById('formPeriodo').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('periodo_id').value;
        const payload = {
            mes: document.getElementById('periodo_mes').value,
            anio: document.getElementById('periodo_anio').value,
            fecha_inicio: document.getElementById('periodo_fecha_inicio').value || null,
            fecha_fin: document.getElementById('periodo_fecha_fin').value || null,
            activo: document.getElementById('periodo_activo').checked ? 1 : 0,
        };
        const url = id ? `/periodos/${id}` : '/periodos';
        const res = await maFetch(url, {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (res && res.ok) {
            maToast('success', res.message);
            modalPeriodo.hide();
            setTimeout(() => location.reload(), 700);
        }
    });

    async function eliminarPeriodo(id, nombre) {
        if (!(await maConfirmarEliminar(nombre))) return;
        const res = await maFetch(`/periodos/${id}`, { method: 'DELETE' });
        if (res && res.ok) {
            maToast('success', res.message);
            setTimeout(() => location.reload(), 700);
        }
    }

    /* ---------------------------------------------------------------
     * Seccion 8: pasar alumnos activos al siguiente periodo.
     * ------------------------------------------------------------- */
    let candidatosPaseActual = [];

    function prepararPaseAlumnos() {
        document.getElementById('pase_periodo_anterior_id').value = '';
        document.getElementById('pase_periodo_nuevo_id').value = '';
        document.getElementById('pase_lista_wrap').classList.add('d-none');
        document.getElementById('pase_estado_vacio').classList.remove('d-none');
        document.getElementById('pase_lista_alumnos').innerHTML = '';
        candidatosPaseActual = [];
    }

    async function cargarCandidatosPase() {
        const periodoId = document.getElementById('pase_periodo_anterior_id').value;
        if (!periodoId) {
            document.getElementById('pase_lista_wrap').classList.add('d-none');
            document.getElementById('pase_estado_vacio').classList.remove('d-none');
            return;
        }

        const res = await maFetch(`/periodos/${periodoId}/candidatos`);
        if (!res || !res.ok) return;

        candidatosPaseActual = res.data;
        const cont = document.getElementById('pase_lista_alumnos');

        if (!candidatosPaseActual.length) {
            document.getElementById('pase_lista_wrap').classList.add('d-none');
            document.getElementById('pase_estado_vacio').classList.remove('d-none');
            document.getElementById('pase_estado_vacio').innerText = 'Ese periodo no tiene alumnos activos registrados.';
            return;
        }

        document.getElementById('pase_estado_vacio').classList.add('d-none');
        document.getElementById('pase_lista_wrap').classList.remove('d-none');

        cont.innerHTML = candidatosPaseActual.map(a => `
            <div class="form-check">
                <input class="form-check-input pase-alumno-checkbox" type="checkbox" value="${a.id}" id="pase_alumno_${a.id}" checked>
                <label class="form-check-label" for="pase_alumno_${a.id}">${a.nombre}</label>
            </div>
        `).join('');
    }

    function marcarTodosPase(marcar) {
        document.querySelectorAll('.pase-alumno-checkbox').forEach(cb => cb.checked = marcar);
    }

    async function confirmarPaseAlumnos() {
        const periodoAnteriorId = document.getElementById('pase_periodo_anterior_id').value;
        const periodoNuevoId = document.getElementById('pase_periodo_nuevo_id').value;

        if (!periodoAnteriorId || !periodoNuevoId) {
            Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Selecciona el periodo anterior y el periodo nuevo.' });
            return;
        }
        if (periodoAnteriorId === periodoNuevoId) {
            Swal.fire({ icon: 'warning', title: 'Periodos iguales', text: 'El periodo anterior y el nuevo deben ser distintos.' });
            return;
        }

        const seleccionados = Array.from(document.querySelectorAll('.pase-alumno-checkbox:checked')).map(cb => cb.value);
        const copiarTalleres = document.getElementById('pase_copiar_talleres').checked;

        const confirmacion = await Swal.fire({
            icon: 'question',
            title: 'Confirmar pase de alumnos',
            text: `${seleccionados.length} alumno(s) pasarán activos al nuevo periodo${copiarTalleres ? ' con sus talleres y mensualidad' : ''}. En su historial, el periodo anterior quedará como inactivo. Los no marcados quedarán inactivos en el nuevo periodo. ¿Continuar?`,
            showCancelButton: true,
            confirmButtonText: 'Si, pasar alumnos',
            cancelButtonText: 'Cancelar',
        });
        if (!confirmacion.isConfirmed) return;

        const res = await maFetch(`/periodos/${periodoNuevoId}/pasar-alumnos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ periodo_anterior_id: periodoAnteriorId, alumno_ids: seleccionados, copiar_talleres: copiarTalleres ? 1 : 0 }),
        });
        if (res && res.ok) {
            maToast('success', res.message, 'Alumnos pasados');
            buscarAlumnosReactivo();
            bootstrap.Modal.getInstance(document.getElementById('modalPaseAlumnos')).hide();
        }
    }
</script>
@endpush