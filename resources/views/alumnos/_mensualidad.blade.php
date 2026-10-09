{{--
    Cobros del taller: modalidad, mensualidad (cada mes) y, al costado, la
    matricula del año (una sola vez por año, por alumno). Se usa en el
    "primer taller" (prefijo alumno) y en "Agregar / editar taller"
    (prefijo taller). Los montos se escriben a mano.
--}}
@php
    $sm = $tamano === 'form-select-sm';
@endphp
<div class="row g-2 mb-2 align-items-start">
    <div class="col-md-4">
        <label class="form-label small fw-semibold" for="{{ $prefijo }}_veces_semana">Modalidad</label>
        <select class="form-select {{ $tamano }}" id="{{ $prefijo }}_veces_semana">
            <option value="">-- Selecciona --</option>
            @foreach([1 => '1 vez por semana', 2 => '2 veces por semana', 3 => '3 veces por semana', 4 => '4 veces por semana', 5 => '5 veces por semana'] as $n => $etiqueta)
                <option value="{{ $n }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold" for="{{ $prefijo }}_monto_mensual">Mensualidad <span class="fw-normal text-muted">(cada mes)</span></label>
        <div class="input-group {{ $sm ? 'input-group-sm' : '' }}">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01" min="0" class="form-control" id="{{ $prefijo }}_monto_mensual" placeholder="0.00">
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold" for="{{ $prefijo }}_matricula_monto">Matrícula <span id="{{ $prefijo }}_matricula_anio">{{ $anioMatricula }}</span> <span class="fw-normal text-muted">(una vez al año)</span></label>
        <div class="input-group {{ $sm ? 'input-group-sm' : '' }}">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01" min="0" class="form-control" id="{{ $prefijo }}_matricula_monto" placeholder="0.00">
        </div>
    </div>

    <div class="col-md-8">
        <input type="text" maxlength="255" class="form-control {{ $sm ? 'form-control-sm' : '' }}" id="{{ $prefijo }}_nota_mensualidad"
               placeholder="Nota de la mensualidad (opcional). Ej.: entró a mitad de mes, paga solo 4 clases" aria-label="Nota de la mensualidad">
    </div>
    <div class="col-md-4">
        <input type="text" maxlength="255" class="form-control {{ $sm ? 'form-control-sm' : '' }}" id="{{ $prefijo }}_matricula_nota"
               placeholder="Nota de la matrícula (opcional)" aria-label="Nota de la matrícula">
    </div>

    <div class="col-md-8"><small class="text-muted">Con la mensualidad se crea su pago de cada mes; la nota también se ve en Pagos.</small></div>
    <div class="col-md-4"><small class="text-muted" id="{{ $prefijo }}_matricula_estado">Déjala vacía si no corresponde.</small></div>
</div>
