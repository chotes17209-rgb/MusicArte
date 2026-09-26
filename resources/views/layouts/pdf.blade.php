{{--
    Plantilla comun de los PDF (dompdf): encabezado con logo y datos del
    reporte, pie con fecha de generacion y numero de pagina.
    Secciones: titulo, subtitulo (opcional), contenido.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo') · MusicArte</title>
    <style>
        @page { margin: 92px 36px 50px 36px; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1d1c1a; margin: 0; }
        .cab { position: fixed; top: -72px; left: 0; right: 0; height: 58px; border-bottom: 2px solid #3d2c8d; }
        .cab td { vertical-align: middle; }
        .cab .logo { width: 44px; height: 44px; border-radius: 8px; }
        .cab .marca { font-size: 14px; font-weight: bold; color: #3d2c8d; }
        .cab .marca-sub { font-size: 8.5px; color: #8a8880; }
        .cab .titulo { font-size: 13px; font-weight: bold; text-align: right; }
        .cab .subtitulo { font-size: 9px; color: #55534d; text-align: right; }
        .pie { position: fixed; bottom: -34px; left: 0; right: 0; height: 20px; border-top: 1px solid #e6e5e0; font-size: 8px; color: #8a8880; padding-top: 5px; }
        .pie .pagina:after { content: "Página " counter(page); }
        table { border-collapse: collapse; width: 100%; }
        .resumen { margin: 4px 0 12px; }
        .resumen td { width: 25%; padding: 8px 10px; border: 1px solid #e6e5e0; background: #fafaf8; }
        .resumen .etq { font-size: 8px; color: #8a8880; text-transform: uppercase; letter-spacing: .4px; }
        .resumen .val { font-size: 15px; font-weight: bold; margin-top: 2px; }
        .resumen .det { font-size: 8px; color: #8a8880; }
        h2 { font-size: 11px; margin: 14px 0 6px; color: #3d2c8d; }
        .tabla th { background: #3d2c8d; color: #fff; font-size: 8.5px; text-align: left; padding: 5px 6px; font-weight: bold; }
        .tabla td { padding: 4px 6px; border-bottom: 1px solid #ecebe7; vertical-align: top; }
        .tabla tr:nth-child(even) td { background: #fafaf8; }
        .tabla tfoot td { font-weight: bold; background: #f1eff8 !important; border-top: 1.5px solid #3d2c8d; }
        .der, .tabla th.der { text-align: right; }
        .centro { text-align: center; }
        .muted { color: #8a8880; }
        .verde { color: #1d7a46; }
        .rojo { color: #b42318; }
        .ambar { color: #946200; }
        .fuerte { font-weight: bold; }
        .barra { height: 6px; background: #ecebe7; border-radius: 3px; }
        .barra div { height: 6px; border-radius: 3px; background: #3d2c8d; }
        .estado { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .estado.pagado, .estado.dictada, .estado.asistio { background: #eaf5ee; color: #1d7a46; }
        .estado.pendiente, .estado.cancelada, .estado.falto { background: #fdeeec; color: #b42318; }
        .estado.a_cuenta, .estado.justificado { background: #fdf4e1; color: #946200; }
        .estado.programada { background: #f0f0ec; color: #55534d; }
        .salto { page-break-before: always; }
        .no-cortar { page-break-inside: avoid; }
        @yield('estilos')
    </style>
</head>
<body>
    <div class="cab">
        <table>
            <tr>
                <td style="width:52px"><img class="logo" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}"></td>
                <td>
                    <div class="marca">MusicArte</div>
                    <div class="marca-sub">Centro Cultural</div>
                </td>
                <td>
                    <div class="titulo">@yield('titulo')</div>
                    <div class="subtitulo">@yield('subtitulo')</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="pie">
        <table><tr>
            <td>Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->name ?? 'MusicArte' }}</td>
            <td class="der"><span class="pagina"></span></td>
        </tr></table>
    </div>

    @yield('contenido')
</body>
</html>
