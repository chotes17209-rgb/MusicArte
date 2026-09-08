<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de alumnos - MusicArte</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #222; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #3d2c8d; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { color: #3d2c8d; font-size: 20px; margin: 0; }
        .header small { color: #666; }
        .filtros { font-size: 12px; color: #444; margin-bottom: 14px; }
        .filtros span { display: inline-block; background: #f1eefb; border: 1px solid #dcd4f5; border-radius: 6px; padding: 3px 10px; margin-right: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #3d2c8d; color: #fff; }
        tr:nth-child(even) { background: #f7f6fc; }
        .estado-activo { color: #157347; font-weight: bold; }
        .estado-inactivo { color: #b02a37; font-weight: bold; }
        .footer { margin-top: 18px; font-size: 11px; color: #777; text-align: right; }
        .btn-imprimir { margin-bottom: 16px; }
        @media print {
            .btn-imprimir { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="btn-imprimir" onclick="window.print()" style="padding:8px 16px;background:#3d2c8d;color:#fff;border:none;border-radius:6px;cursor:pointer;">Imprimir</button>

    <div class="header">
        <div>
            <h1>MUSICARTE — Centro Cultural</h1>
            <small>Lista de alumnos</small>
        </div>
        <small>Generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>

    <div class="filtros">
        Filtros aplicados:
        <span>Periodo: {{ $periodo ? $periodo->nombre : 'Todos' }}</span>
        <span>Maestro: {{ $maestro ? $maestro->nombre : 'Todos' }}</span>
        <span>Especialidad/Taller: {{ $especialidad ? $especialidad->nombre : 'Todas' }}</span>
        <span>Total: {{ $alumnos->count() }} alumno(s)</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>DNI</th>
                <th>Edad</th>
                <th>Tutor</th>
                <th>Celular</th>
                <th>Talleres activos</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alumnos as $i => $alumno)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $alumno->nombre }}</td>
                    <td>{{ $alumno->dni ?? '—' }}</td>
                    <td>{{ $alumno->edad ?? '—' }}</td>
                    <td>{{ $alumno->tutor ?? '—' }}</td>
                    <td>{{ $alumno->celular ?? '—' }}</td>
                    <td>{{ $alumno->talleres_activos_count }}</td>
                    <td class="{{ $alumno->activo ? 'estado-activo' : 'estado-inactivo' }}">
                        {{ $alumno->activo ? 'Activo' : 'Inactivo' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No hay alumnos que coincidan con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Sistema de gestion MusicArte</div>
</body>
</html>
