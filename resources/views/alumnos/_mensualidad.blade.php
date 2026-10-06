{{--
    Modalidad (veces por semana) y mensualidad de un taller. Se usa en el
    "primer taller" (prefijo alumno) y en "Agregar taller" (prefijo taller).
    La mensualidad se escribe a mano segun la modalidad que eligio el alumno,
    con una nota opcional (ej. entro a mitad de mes y paga menos).
--}}
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
    <div class="col-md-3">
        <label class="form-label small fw-semibold" for="{{ $prefijo }}_monto_mensual">Mensualidad</label>
        <div class="input-group {{ $tamano === 'form-select-sm' ? 'input-group-sm' : '' }}">
            <span class="input-group-text">S/</span>
            <input type="number" step="0.01" min="0" class="form-control" id="{{ $prefijo }}_monto_mensual" placeholder="0.00">
        </div>
    </div>
    <div class="col-md-5">
        <label class="form-label small fw-semibold" for="{{ $prefijo }}_nota_mensualidad">Nota <span class="fw-normal text-muted">(opcional)</span></label>
        <input type="text" maxlength="255" class="form-control {{ $tamano === 'form-select-sm' ? 'form-control-sm' : '' }}" id="{{ $prefijo }}_nota_mensualidad"
               placeholder="Ej.: entró a mitad de mes, paga solo 4 clases">
    </div>
    <div class="col-12"><small class="text-muted">Lo que pagará al mes por este taller, según la modalidad que eligió. Con esto se crea su pago del mes; la nota también se ve en Pagos.</small></div>
</div>
