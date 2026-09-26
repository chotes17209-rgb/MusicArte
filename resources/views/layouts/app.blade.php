<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') - MusicArte</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#2a1e63">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#2a1e63">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ma-morado: #3d2c8d;
            --ma-morado-oscuro: #2a1e63;
            --ma-morado-suave: #efecfb;
            --ma-dorado: #f2b134;
            --ma-gris: #f5f6fa;
            --ma-borde: #e8e8f0;
            --ma-texto: #1f1d2e;
            --ma-texto-suave: #6b6b80;
            --ma-radio: 14px;
            --ma-sombra: 0 1px 2px rgba(20, 16, 50, .04), 0 2px 8px rgba(20, 16, 50, .05);
            --ma-sombra-hover: 0 4px 10px rgba(20, 16, 50, .06), 0 12px 28px rgba(61, 44, 141, .10);

            --bs-primary: #3d2c8d;
            --bs-primary-rgb: 61, 44, 141;
            --bs-link-color: #3d2c8d;
            --bs-link-color-rgb: 61, 44, 141;
            --bs-link-hover-color: #2a1e63;
            --bs-link-hover-color-rgb: 42, 30, 99;
            --bs-body-color: #1f1d2e;
            --bs-border-color: #e8e8f0;
            --bs-border-radius: 10px;
        }
        html, body { overflow-x: hidden; }
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--ma-gris); color: var(--ma-texto);
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6 { letter-spacing: -.015em; }
        a { text-decoration: none; }
        .text-muted { color: var(--ma-texto-suave) !important; }

        /* ---------------- Sidebar ---------------- */
        .sidebar {
            width: 250px; height: 100vh;
            background: radial-gradient(120% 60% at 0% 0%, #4a36a8 0%, transparent 60%), linear-gradient(180deg, #221a52 0%, var(--ma-morado-oscuro) 55%, #1c1545 100%);
            position: fixed; top: 0; left: 0; z-index: 1030;
            display: flex; flex-direction: column;
            transition: transform .25s ease, width .25s ease;
            box-shadow: 4px 0 24px rgba(20, 16, 50, .12);
        }
        .sidebar .logo-box { flex: 0 0 auto; padding: 1.4rem 1rem 1.1rem; text-align: center; }
        .sidebar .logo-box img { width: 64px; height: 64px; border-radius: 18px; object-fit: cover; box-shadow: 0 0 0 3px rgba(242,177,52,.55), 0 8px 20px rgba(0,0,0,.25); }
        .sidebar .logo-box h6 { color: #fff; margin: .7rem 0 0; font-weight: 800; letter-spacing: 2px; font-size: .95rem; }
        .sidebar .logo-box small { color: rgba(255,255,255,.55) !important; font-size: .75rem; }

        /* Nav con scroll propio: asi siempre se puede bajar a ver todas las opciones */
        .sidebar nav {
            flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; flex-wrap: nowrap;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.2) transparent;
            padding: 0 .75rem 1rem;
        }
        .sidebar nav::-webkit-scrollbar { width: 5px; }
        .sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 10px; }

        .sidebar .nav-link {
            color: rgba(255,255,255,.72); padding: .6rem .85rem; margin: 2px 0; font-size: .9rem; font-weight: 500;
            display: flex; align-items: center; gap: .7rem; border-radius: 10px; white-space: nowrap;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar .nav-link i { font-size: 1.05rem; width: 20px; text-align: center; flex-shrink: 0; transition: color .15s ease; }
        .sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.07); }
        .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.13); box-shadow: inset 0 0 0 1px rgba(255,255,255,.06); }
        .sidebar .nav-link.active i { color: var(--ma-dorado); }
        .sidebar .nav-section { color: rgba(255,255,255,.38); font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; padding: 1.1rem .85rem .35rem; }

        .content-wrap { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .25s ease; }

        /* Estado colapsado (solo iconos, texto oculto de verdad) */
        .sidebar.collapsed { width: 78px; }
        .sidebar.collapsed .logo-box h6,
        .sidebar.collapsed .logo-box small,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-link .nav-text { display: none; }
        .sidebar.collapsed .logo-box img { width: 42px; height: 42px; border-radius: 12px; }
        .sidebar.collapsed nav { padding: 0 .6rem 1rem; }
        .sidebar.collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .content-wrap.collapsed { margin-left: 78px; }

        .btn-collapse-sidebar {
            position: absolute; top: 1.2rem; right: -13px; width: 26px; height: 26px;
            border-radius: 50%; background: #fff; color: var(--ma-morado); border: 1px solid var(--ma-borde);
            display: flex; align-items: center; justify-content: center; z-index: 1031;
            box-shadow: 0 2px 8px rgba(20,16,50,.15); font-size: .8rem;
        }
        .btn-collapse-sidebar:hover { background: var(--ma-dorado); color: #3a2900; border-color: var(--ma-dorado); }

        /* ---------------- Topbar ---------------- */
        .topbar {
            background: rgba(255,255,255,.82); backdrop-filter: saturate(180%) blur(12px); -webkit-backdrop-filter: saturate(180%) blur(12px);
            border-bottom: 1px solid var(--ma-borde); padding: .75rem 1.75rem;
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        .topbar h5 { font-weight: 700 !important; font-size: 1.15rem; }
        .topbar .btn-light { background: #fff; border: 1px solid var(--ma-borde); border-radius: 12px; }
        .topbar .btn-light:hover { background: var(--ma-morado-suave); border-color: #dcd6f5; }
        .main-content { padding: 1.75rem; flex: 1; }

        /* ---------------- Botones ---------------- */
        .btn { border-radius: 10px; font-weight: 600; font-size: .9rem; transition: all .15s ease; }
        .btn-sm { border-radius: 8px; font-size: .8rem; }
        .btn-morado, .btn-primary {
            background: linear-gradient(135deg, #4b37a9, var(--ma-morado)); border: 0; color: #fff;
            box-shadow: 0 1px 2px rgba(61,44,141,.25), 0 4px 12px rgba(61,44,141,.18);
        }
        .btn-morado:hover, .btn-primary:hover, .btn-morado:focus, .btn-primary:focus {
            background: linear-gradient(135deg, var(--ma-morado), var(--ma-morado-oscuro)); color: #fff;
            transform: translateY(-1px); box-shadow: 0 2px 4px rgba(61,44,141,.25), 0 8px 18px rgba(61,44,141,.22);
        }
        .btn-outline-primary { --bs-btn-color: var(--ma-morado); --bs-btn-border-color: #cfc7f0; --bs-btn-hover-bg: var(--ma-morado); --bs-btn-hover-border-color: var(--ma-morado); --bs-btn-active-bg: var(--ma-morado-oscuro); }
        .btn-outline-secondary { --bs-btn-color: #4a4a5e; --bs-btn-border-color: var(--ma-borde); --bs-btn-hover-bg: var(--ma-morado-suave); --bs-btn-hover-color: var(--ma-morado); --bs-btn-hover-border-color: #dcd6f5; }
        .btn-light { --bs-btn-bg: #f3f2f9; --bs-btn-border-color: transparent; --bs-btn-hover-bg: var(--ma-morado-suave); --bs-btn-hover-border-color: transparent; }
        .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; padding: 0; }

        /* ---------------- Formularios ---------------- */
        .form-control, .form-select { border-radius: 10px; border-color: var(--ma-borde); padding: .5rem .85rem; font-size: .9rem; background-color: #fff; }
        .form-select { padding-right: 2.25rem; background-position: right .7rem center; }
        .form-control-sm, .form-select-sm { border-radius: 8px; padding: .3rem .65rem; font-size: .82rem; }
        .form-select-sm { padding-right: 2rem; }
        .form-control:focus, .form-select:focus, .form-check-input:focus { border-color: #a99be3; box-shadow: 0 0 0 4px rgba(61,44,141,.12); }
        .form-check-input:checked { background-color: var(--ma-morado); border-color: var(--ma-morado); }
        .form-label { font-weight: 600; font-size: .82rem; color: #3b3a4d; }
        .input-group-text { border-color: var(--ma-borde); background: #f7f7fb; border-radius: 10px; }

        /* ---------------- Tarjetas ---------------- */
        .card { border-radius: var(--ma-radio); border: 1px solid rgba(232,232,240,.7); box-shadow: var(--ma-sombra); transition: box-shadow .2s ease, transform .2s ease; }
        .card-header { background: transparent; border-bottom-color: var(--ma-borde); font-weight: 700; }
        .card-kpi { border: none; border-radius: 16px; box-shadow: var(--ma-sombra); position: relative; overflow: hidden; }
        .card-kpi::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; background: linear-gradient(90deg, var(--ma-morado), #7b5ce0, var(--ma-dorado)); opacity: .9; }
        .card.p-3:hover, .card.p-4:hover, .card-kpi:hover { box-shadow: var(--ma-sombra-hover); }
        a .card:hover { transform: translateY(-2px); }

        /* ---------------- Tablas ---------------- */
        .table { --bs-table-hover-bg: #faf9ff; margin-bottom: 0; font-size: .9rem; }
        .table > :not(caption) > * > * { padding: .65rem .7rem; border-bottom-color: #f0f0f5; }
        .table-sm > :not(caption) > * > * { padding: .4rem .5rem; }
        .table td { vertical-align: middle; }
        .table thead th { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #8a8aa0; background: #fafafd; border-bottom: 1px solid var(--ma-borde); white-space: nowrap; }
        .table tbody tr { transition: background .12s ease; }
        .table tbody tr:hover > * { background: #faf9ff; }

        /* ---------------- Badges (estilo suave) ---------------- */
        .badge { font-weight: 600; letter-spacing: .2px; border-radius: 999px; padding: .38em .75em; }
        .badge.bg-success:not(.rounded-pill) { background: #e3f5ea !important; color: #17803d !important; }
        .badge.bg-danger:not(.rounded-pill) { background: #fde8ea !important; color: #c0283a !important; }
        .badge.bg-warning:not(.rounded-pill) { background: #fff3d6 !important; color: #a26500 !important; }
        .badge.bg-secondary:not(.rounded-pill) { background: #eeeef3 !important; color: #55556a !important; }
        .badge.bg-info:not(.rounded-pill) { background: #e1f3fb !important; color: #0e6f91 !important; }
        .badge.bg-primary:not(.rounded-pill) { background: var(--ma-morado-suave) !important; color: var(--ma-morado) !important; }
        .badge-rol-admin { background: var(--ma-dorado); color: #3a2900; }
        .badge-rol-recepcion { background: #6c757d; }

        /* ---------------- Pestanas, paginacion, modales, menus ---------------- */
        .nav-tabs { border-bottom: 1px solid var(--ma-borde); gap: .25rem; }
        .nav-tabs .nav-link { border: 0; border-radius: 10px 10px 0 0; color: var(--ma-texto-suave); font-weight: 600; padding: .6rem 1rem; border-bottom: 2px solid transparent; }
        .nav-tabs .nav-link:hover { color: var(--ma-morado); background: var(--ma-morado-suave); }
        .nav-tabs .nav-link.active { color: var(--ma-morado); background: transparent; border-bottom-color: var(--ma-morado); }
        .nav-pills .nav-link.active { background: var(--ma-morado); }
        .pagination { gap: .25rem; }
        .page-link { border-radius: 8px !important; border-color: var(--ma-borde); color: var(--ma-morado); font-weight: 600; font-size: .85rem; }
        .page-item.active .page-link { background: var(--ma-morado); border-color: var(--ma-morado); }
        .modal-content { border: 0; border-radius: 18px; box-shadow: 0 24px 60px rgba(20,16,50,.25); overflow: hidden; }
        .modal-header { border-bottom-color: var(--ma-borde); padding: 1rem 1.4rem; }
        .modal-body { padding: 1.4rem; }
        .modal-footer { border-top-color: var(--ma-borde); background: #fafafd; }
        .modal-backdrop.show { opacity: .4; }
        .dropdown-menu { border: 1px solid var(--ma-borde); border-radius: 12px; box-shadow: 0 12px 32px rgba(20,16,50,.12); padding: .4rem; }
        .dropdown-item { border-radius: 8px; padding: .5rem .75rem; font-size: .9rem; }
        .alert { border-radius: 12px; border: 0; }
        .swal2-popup { border-radius: 18px !important; font-family: inherit !important; }
        .swal2-styled.swal2-confirm { border-radius: 10px !important; }
        .swal2-styled.swal2-cancel { border-radius: 10px !important; }

        .aviso-flotante { border-left: 5px solid var(--ma-morado); }
        .aviso-urgente { border-left-color: #dc3545 !important; }
        .aviso-advertencia { border-left-color: #f2b134 !important; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); width: 250px !important; box-shadow: none; }
            .sidebar.show { box-shadow: 4px 0 24px rgba(20, 16, 50, .25); }
            .sidebar.show { transform: translateX(0); }
            .sidebar.collapsed .nav-link .nav-text,
            .sidebar.collapsed .logo-box h6,
            .sidebar.collapsed .logo-box small,
            .sidebar.collapsed .nav-section { display: inline; }
            .sidebar.collapsed .nav-link { justify-content: flex-start; padding-left: .85rem; }
            .content-wrap, .content-wrap.collapsed { margin-left: 0; }
            .btn-collapse-sidebar { display: none !important; }
            .topbar { padding: .65rem 1rem; }
            .main-content { padding: 1rem; }
        }
        .toggler-mobile { display: none; }
        @media (max-width: 991px) { .toggler-mobile { display: inline-flex; } }

        /* ==============================================================
         * Facil de entender (sin cambiar la logica de ninguna pantalla)
         * ============================================================== */

        /* Encabezados de tabla legibles (no en mayusculas diminutas). */
        .table thead th { text-transform: none; letter-spacing: 0; font-size: .8rem; font-weight: 700; color: #4a4a5e; }
        .table tbody td.fw-semibold, .table tbody td:first-child { color: var(--ma-texto); }

        /* Botones de accion en las tablas: icono + texto, con color segun lo que hacen. */
        .table .btn-icon { width: auto; height: 32px; padding: 0 .65rem; gap: .35rem; font-size: .78rem; font-weight: 600; border: 0; }
        .table .btn-icon:has(.bi-eye)::after { content: "Ver"; }
        .table .btn-icon:has(.bi-pencil)::after { content: "Editar"; }
        .table .btn-icon:has(.bi-trash)::after { content: "Eliminar"; }
        .table .btn-icon:has(.bi-cash-stack)::after { content: "Abonos"; }
        .table .btn-icon:has(.bi-file-earmark-pdf)::after { content: "PDF"; }
        .table .btn-icon:has(.bi-eye) { background: #e7f0fd; color: #1d5fa8; }
        .table .btn-icon:has(.bi-pencil) { background: var(--ma-morado-suave); color: var(--ma-morado); }
        .table .btn-icon:has(.bi-trash) { background: #fdecee; color: #c0283a !important; }
        .table .btn-icon:has(.bi-cash-stack) { background: #e3f5ea; color: #17803d; }
        .table .btn-icon:has(.bi-file-earmark-pdf) { background: #f1f1f6; color: #4a4a5e; }
        .table .btn-icon:hover { filter: brightness(.95); transform: translateY(-1px); }
        .table td:has(.btn-icon) { white-space: nowrap; }
        .table td.text-end, .table th.text-end { white-space: nowrap; }

        /* Enlaces de navegacion ("Volver a...", "Ver historial...") como botones visibles. */
        .btn-volver { background: #fff; border: 1px solid var(--ma-borde); color: #4a4a5e; }
        .btn-volver:hover { background: var(--ma-morado-suave); color: var(--ma-morado); border-color: #dcd6f5; }
        .btn-ir { background: var(--ma-morado-suave); color: var(--ma-morado); border: 1px solid #dcd6f5; }
        .btn-ir:hover { background: var(--ma-morado); color: #fff; border-color: var(--ma-morado); }

        /* Fila destacada (ej. el periodo en curso). */
        .table tr.fila-actual > * { background: #f7f5ff; }
        .table tr.fila-actual > td:first-child { box-shadow: inset 3px 0 0 var(--ma-morado); }

        /* Celular: cada fila de tabla se ve como una tarjeta con "Campo: valor". */
        @media (max-width: 767px) {
            table.tabla-tarjetas thead { display: none; }
            table.tabla-tarjetas, table.tabla-tarjetas tbody, table.tabla-tarjetas tr, table.tabla-tarjetas td { display: block; width: 100%; }
            table.tabla-tarjetas tbody tr { background: #fff; border: 1px solid var(--ma-borde); border-radius: 14px; margin-bottom: .75rem; padding: .6rem .85rem; box-shadow: var(--ma-sombra); }
            table.tabla-tarjetas tbody tr:hover > * { background: transparent; }
            table.tabla-tarjetas td { border: 0 !important; padding: .3rem 0 !important; }
            table.tabla-tarjetas td[data-label] { position: relative; padding-left: 42% !important; text-align: right !important; min-height: 1.9rem; }
            table.tabla-tarjetas td[data-label]::before { content: attr(data-label); position: absolute; left: 0; top: .35rem; width: 40%; text-align: left; font-size: .78rem; font-weight: 600; color: var(--ma-texto-suave); }
            table.tabla-tarjetas td.td-titulo { padding-left: 0 !important; text-align: left !important; font-size: 1rem; font-weight: 700; padding-bottom: .45rem !important; margin-bottom: .2rem; border-bottom: 1px solid #f0f0f5 !important; }
            table.tabla-tarjetas td.td-titulo::before, table.tabla-tarjetas td.td-acciones::before { display: none; }
            table.tabla-tarjetas td.td-acciones { padding-left: 0 !important; text-align: left !important; white-space: normal; padding-top: .6rem !important; margin-top: .3rem; border-top: 1px solid #f0f0f5 !important; }
            table.tabla-tarjetas td.td-acciones .btn { margin: 0 .25rem .25rem 0 !important; }
            .card:has(> .table-responsive > table.tabla-tarjetas), .card:has(> table.tabla-tarjetas) { background: transparent; border: 0; box-shadow: none; padding: 0 !important; }
        }

        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-thumb { background: #d7d3ee; border-radius: 10px; }

        /* --------------------------------------------------------------
         * Tablero de horarios por maestro: replica el cuadro fisico de
         * horarios (hoja pegada en salon), pero digital y responsive.
         * -------------------------------------------------------------- */
        .tablero-maestro { border: 1px solid #e3e1f2; border-radius: 12px; overflow: hidden; margin-bottom: 1.25rem; background: #fff; }
        .tablero-header { background: linear-gradient(90deg, var(--ma-morado), var(--ma-morado-oscuro)); color: #fff; padding: .6rem 1rem; display: flex; align-items: baseline; gap: .6rem; flex-wrap: wrap; }
        .tablero-titulo { font-weight: 700; letter-spacing: .3px; font-size: .95rem; }
        .tablero-sub { font-size: .78rem; opacity: .85; }
        table.tablero-tabla { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        table.tablero-tabla th { background: #f1eefb; color: #3d2c8d; font-size: .72rem; text-transform: uppercase; text-align: center; padding: .5rem .35rem; border-bottom: 1px solid #e3e1f2; white-space: nowrap; }
        table.tablero-tabla td { border: 1px solid #eeecf8; padding: .4rem .45rem; font-size: .82rem; vertical-align: top; }
        table.tablero-tabla td.col-hora { background: #faf9fd; font-weight: 600; color: #3d2c8d; text-align: center; white-space: nowrap; width: 90px; }
        table.tablero-tabla td.celda-ocupada { background: #fbf6df; }
        .alumno-celda { line-height: 1.3; }
        .alumno-celda + .alumno-celda { margin-top: 4px; padding-top: 4px; border-top: 1px dashed #e3d9a8; }
        .alumno-celda.inactivo { color: #b02a37; text-decoration: line-through; opacity: .75; }
        .edad-celda { color: #6b6b80; font-size: .78em; margin-left: 2px; }
        @media (max-width: 575px) {
            table.tablero-tabla th, table.tablero-tabla td { font-size: .74rem; padding: .3rem; }
        }
    </style>
    @stack('estilos')
</head>
<body>

<div class="sidebar" id="sidebar">
    <button class="btn-collapse-sidebar d-none d-lg-flex" id="btnCollapseSidebar" title="Contraer menu">
        <i class="bi bi-chevron-left" id="iconCollapseSidebar"></i>
    </button>
    <div class="logo-box">
        <img src="{{ asset('images/logo.png') }}" alt="MusicArte">
        <h6>MUSICARTE</h6>
        <small class="text-white-50">Centro Cultural</small>
    </div>
    <nav class="nav flex-column py-2">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> <span class="nav-text">Dashboard</span></a>

        <div class="nav-section">Academico</div>
        <a class="nav-link {{ request()->routeIs('alumnos.*') || request()->routeIs('periodos.*') ? 'active' : '' }}" href="{{ route('alumnos.index') }}"><i class="bi bi-people"></i> <span class="nav-text">Alumnos</span></a>
        <a class="nav-link {{ request()->routeIs('maestros.*') ? 'active' : '' }}" href="{{ route('maestros.index') }}"><i class="bi bi-person-badge"></i> <span class="nav-text">Maestros</span></a>
        <a class="nav-link {{ request()->routeIs('especialidades.*') ? 'active' : '' }}" href="{{ route('especialidades.index') }}"><i class="bi bi-music-note-list"></i> <span class="nav-text">Especialidades</span></a>

        <div class="nav-section">Clases</div>
        <a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}"><i class="bi bi-calendar3"></i> <span class="nav-text">Calendario</span></a>
        <a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.index') }}"><i class="bi bi-clock-history"></i> <span class="nav-text">Horarios</span></a>
        <a class="nav-link {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.index') }}"><i class="bi bi-clipboard-check"></i> <span class="nav-text">Asistencia</span></a>
        <a class="nav-link {{ request()->routeIs('recitales.*') ? 'active' : '' }}" href="{{ route('recitales.index') }}"><i class="bi bi-mic"></i> <span class="nav-text">Recitales/Eventos</span></a>

        <div class="nav-section">Administracion</div>
        <a class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}" href="{{ route('pagos.index') }}"><i class="bi bi-cash-coin"></i> <span class="nav-text">Pagos</span></a>
        <a class="nav-link {{ request()->routeIs('egresos.*') ? 'active' : '' }}" href="{{ route('egresos.index') }}"><i class="bi bi-receipt"></i> <span class="nav-text">Egresos</span></a>
        <a class="nav-link {{ request()->routeIs('caja-chica.*') ? 'active' : '' }}" href="{{ route('caja-chica.index') }}"><i class="bi bi-wallet2"></i> <span class="nav-text">Caja Chica</span></a>
        <a class="nav-link {{ request()->routeIs('planilla.*') ? 'active' : '' }}" href="{{ route('planilla.index') }}"><i class="bi bi-file-earmark-person"></i> <span class="nav-text">Planilla Maestros</span></a>

        <div class="nav-section">General</div>
        <a class="nav-link {{ request()->routeIs('avisos.*') ? 'active' : '' }}" href="{{ route('avisos.index') }}"><i class="bi bi-megaphone"></i> <span class="nav-text">Avisos</span></a>
        <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}"><i class="bi bi-graph-up"></i> <span class="nav-text">Reportes</span></a>
    </nav>
</div>

<div class="content-wrap" id="contentWrap">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light toggler-mobile" id="btnToggleSidebar"><i class="bi bi-list fs-4"></i></button>
            <h5 class="mb-0 fw-semibold text-dark">@yield('titulo', 'Panel')</h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light position-relative btn-icon" id="btnAvisos" title="Avisos">
                <i class="bi bi-bell fs-5"></i>
                @if(($avisosFlotantes ?? collect())->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $avisosFlotantes->count() }}</span>
                @endif
            </button>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <span class="badge {{ auth()->user()->esAdmin() ? 'badge-rol-admin' : 'badge-rol-recepcion' }}">{{ auth()->user()->rolLabel() }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        @yield('contenido')
    </div>
</div>

<!-- ===================== MODAL VENTANA FLOTANTE DE AVISOS ===================== -->
<div class="modal fade" id="modalAvisos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--ma-morado); color: #fff;">
                <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Avisos del Centro Cultural</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @forelse(($avisosFlotantes ?? collect()) as $aviso)
                    <div class="alert aviso-flotante aviso-{{ $aviso->tipo }} mb-2" data-aviso-id="{{ $aviso->id }}">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $aviso->titulo }}</strong>
                            <button class="btn-close" style="font-size:.7rem" onclick="descartarAviso({{ $aviso->id }}, this)"></button>
                        </div>
                        <div class="small text-muted mt-1">{!! nl2br(e($aviso->mensaje)) !!}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No hay avisos activos por el momento.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    // ---------- Helpers globales de UI (toasts, confirmaciones, fetch AJAX) ----------
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3200, timerProgressBar: true,
    });

    function maToast(icon, message) { Toast.fire({ icon, title: message }); }

    // Evita que un doble clic (o doble tap) dispare dos peticiones identicas
    // en simultaneo. Si ya hay una peticion en curso al mismo metodo+URL,
    // la segunda llamada reutiliza la promesa de la primera en vez de
    // abrir una conexion nueva (esto era lo que causaba los 499 en produccion).
    const _maFetchEnCurso = new Map();

    function maFetch(url, options = {}) {
        const clave = `${options.method || 'GET'} ${url}`;
        if (_maFetchEnCurso.has(clave)) {
            return _maFetchEnCurso.get(clave);
        }

        const promesa = _maFetchEjecutar(url, options).finally(() => {
            _maFetchEnCurso.delete(clave);
        });

        _maFetchEnCurso.set(clave, promesa);
        return promesa;
    }

    function _esperar(ms) { return new Promise(r => setTimeout(r, ms)); }

    async function _maFetchEjecutar(url, options) {
        options.headers = Object.assign({
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }, options.headers || {});

        const metodo = (options.method || 'GET').toUpperCase();
        const esperas = [250, 600]; // ms antes de cada reintento

        try {
            let res = await fetch(url, options);

            // El borde de Railway a veces corta la conexion justo al reusarla
            // (499) sin que la peticion llegue a la app. Como un GET no
            // modifica nada, es seguro reintentarlo en silencio. Se espera
            // un poco entre intentos para no reusar la misma conexion rota.
            let intento = 0;
            while (res.status === 499 && metodo === 'GET' && intento < esperas.length) {
                await _esperar(esperas[intento]);
                res = await fetch(url, options);
                intento++;
            }

            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    const msgs = Object.values(data.errors).flat().join('<br>');
                    Swal.fire({ icon: 'warning', title: 'Revisa el formulario', html: msgs });
                } else {
                    Swal.fire({ icon: 'error', title: 'No se pudo completar', text: data.message || 'Ocurrio un error inesperado.' });
                }
                return null;
            }
            return data;
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error de conexion', text: 'Revisa tu conexion e intenta de nuevo.' });
            return null;
        }
    }

    function maConfirmarEliminar(nombre = 'este registro') {
        return Swal.fire({
            icon: 'warning',
            title: '¿Eliminar?',
            html: `Vas a eliminar <b>${nombre}</b>. Esta accion no se puede deshacer.`,
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => r.isConfirmed);
    }

    async function descartarAviso(id, btn) {
        await maFetch(`/avisos/${id}/descartar`, { method: 'POST' });
        btn.closest('.aviso-flotante').remove();
    }

    document.getElementById('btnAvisos')?.addEventListener('click', () => {
        new bootstrap.Modal('#modalAvisos').show();
    });

    document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    document.getElementById('btnCollapseSidebar')?.addEventListener('click', () => {
        const collapsed = document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('contentWrap')?.classList.toggle('collapsed');
        localStorage.setItem('ma_sidebar_collapsed', collapsed ? '1' : '0');
        document.getElementById('iconCollapseSidebar').className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    });

    if (localStorage.getItem('ma_sidebar_collapsed') === '1' && window.innerWidth >= 992) {
        document.getElementById('sidebar').classList.add('collapsed');
        document.getElementById('contentWrap')?.classList.add('collapsed');
        const iconInicial = document.getElementById('iconCollapseSidebar');
        if (iconInicial) iconInicial.className = 'bi bi-chevron-right';
    }

    // En celular las tablas se muestran como tarjetas: cada celda recibe
    // el nombre de su columna (data-label) para mostrar "Campo: valor".
    // Tambien corre sobre tablas que se recargan por AJAX (filtros).
    function maTablasTarjetas() {
        document.querySelectorAll('table.table:not(.tablero-tabla)').forEach(tabla => {
            const columnas = [...tabla.querySelectorAll('thead tr:last-child th')].map(th => th.textContent.replace(/\s+/g, ' ').trim());
            if (!columnas.length) return;
            tabla.classList.add('tabla-tarjetas');
            tabla.querySelectorAll('tbody tr').forEach(tr => {
                const celdas = [...tr.children];
                if (celdas.length !== columnas.length || celdas[0].dataset.label !== undefined || celdas[0].classList.contains('td-titulo')) return;
                celdas.forEach((td, i) => {
                    if (i === 0) { td.classList.add('td-titulo'); return; }
                    if (/^acci/i.test(columnas[i]) || (!columnas[i] && td.querySelector('.btn'))) { td.classList.add('td-acciones'); return; }
                    td.dataset.label = columnas[i];
                });
            });
        });
    }
    let _maTarjetasPendiente = null;
    new MutationObserver(() => {
        clearTimeout(_maTarjetasPendiente);
        _maTarjetasPendiente = setTimeout(maTablasTarjetas, 50);
    }).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('DOMContentLoaded', maTablasTarjetas);

    // Muestra automaticamente el popup de avisos urgentes al cargar el dashboard
    @if(($avisosFlotantes ?? collect())->where('tipo', 'urgente')->count() > 0 && request()->routeIs('dashboard'))
        window.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal('#modalAvisos').show();
        });
    @endif

    // Mensajes flash de sesion (redirects normales)
    @if(session('success'))
        window.addEventListener('DOMContentLoaded', () => maToast('success', @json(session('success'))));
    @endif
    @if(session('error'))
        window.addEventListener('DOMContentLoaded', () => maToast('error', @json(session('error'))));
    @endif
</script>
@stack('scripts')
</body>
</html>