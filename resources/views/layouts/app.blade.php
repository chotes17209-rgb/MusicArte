<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') · MusicArte</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="MusicArte">
    <link rel="apple-touch-icon" href="{{ asset('images/app-icon-192.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>
    <!-- Tipografia -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ==================================================================
         * Sistema visual de MusicArte
         * Superficies planas, bordes de 1px, un solo color de acento (el
         * morado de la marca) usado solo para acciones principales y estado
         * seleccionado. Todo lo demas en grises neutros.
         * ================================================================== */
        :root {
            --fondo: #f6f6f4;
            --superficie: #ffffff;
            --superficie-2: #fafaf8;
            --borde: #e6e5e0;
            --borde-fuerte: #d4d3cd;
            --texto: #1d1c1a;
            --texto-2: #55534d;
            --texto-3: #8a8880;
            --acento: #3d2c8d;
            --acento-hover: #30226f;
            --acento-suave: #f1eff8;
            --verde: #1d7a46;   --verde-suave: #eaf5ee;
            --rojo: #b42318;    --rojo-suave: #fdeeec;
            --ambar: #946200;   --ambar-suave: #fdf4e1;
            --azul: #1f5fae;    --azul-suave: #eaf1fb;
            --radio: 8px;
            --radio-sm: 6px;
            --sidebar-ancho: 232px;

            /* Compatibilidad con clases viejas de las vistas. */
            --ma-morado: var(--acento);
            --ma-morado-oscuro: var(--acento-hover);
            --ma-morado-suave: var(--acento-suave);
            --ma-dorado: #f2b134;
            --ma-borde: var(--borde);
            --ma-texto-suave: var(--texto-3);
            --ma-sombra: none;

            --bs-body-font-family: 'IBM Plex Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            --bs-body-font-size: .875rem;
            --bs-body-color: var(--texto);
            --bs-body-bg: var(--fondo);
            --bs-border-color: var(--borde);
            --bs-primary: var(--acento);
            --bs-primary-rgb: 61, 44, 141;
            --bs-link-color: var(--acento);
            --bs-link-color-rgb: 61, 44, 141;
            --bs-link-hover-color: var(--acento-hover);
            --bs-link-hover-color-rgb: 48, 34, 111;
            --bs-secondary-color: var(--texto-3);
            --bs-border-radius: var(--radio-sm);
        }
        html, body { overflow-x: hidden; }
        body { background: var(--fondo); color: var(--texto); -webkit-font-smoothing: antialiased; font-size: .875rem; line-height: 1.5; }
        h1, h2, h3, h4, h5, h6 { color: var(--texto); font-weight: 600; letter-spacing: -.01em; }
        h4, .h4 { font-size: 1.25rem; }
        h5, .h5 { font-size: 1.0625rem; }
        h6, .h6 { font-size: .9375rem; }
        .fs-3 { font-size: 1.5rem !important; }
        .fw-bold { font-weight: 600 !important; }
        a { text-decoration: none; }
        .text-muted { color: var(--texto-3) !important; }
        .small, small { font-size: .8125rem; }
        .text-success { color: var(--verde) !important; }
        .text-danger { color: var(--rojo) !important; }
        .text-warning { color: var(--ambar) !important; }
        .bi { vertical-align: -.125em; }

        /* ---------------- Barra lateral ---------------- */
        .sidebar {
            width: var(--sidebar-ancho); height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030;
            display: flex; flex-direction: column;
            background: var(--superficie); border-right: 1px solid var(--borde);
            transition: transform .2s ease, width .2s ease;
        }
        .sidebar .logo-box { display: flex; align-items: center; gap: .65rem; padding: 0 1rem; height: 56px; border-bottom: 1px solid var(--borde); flex: 0 0 auto; }
        .sidebar .logo-box img { width: 30px; height: 30px; border-radius: 6px; object-fit: cover; }
        .sidebar .logo-box .marca { line-height: 1.15; min-width: 0; }
        .sidebar .logo-box .marca strong { display: block; font-size: .9rem; font-weight: 600; color: var(--texto); }
        .sidebar .logo-box .marca span { display: block; font-size: .72rem; color: var(--texto-3); }
        .sidebar nav { flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; flex-wrap: nowrap; padding: .5rem .5rem 1rem; scrollbar-width: thin; }
        .sidebar .nav-section { font-size: .7rem; font-weight: 600; color: var(--texto-3); padding: 1rem .65rem .35rem; text-transform: uppercase; letter-spacing: .04em; }
        .sidebar .nav-link {
            display: flex; align-items: center; gap: .6rem; padding: .42rem .65rem; margin: 1px 0;
            color: var(--texto-2); font-size: .85rem; font-weight: 500; border-radius: var(--radio-sm); white-space: nowrap;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 18px; text-align: center; color: var(--texto-3); flex-shrink: 0; }
        .sidebar .nav-link:hover { background: var(--superficie-2); color: var(--texto); }
        .sidebar .nav-link.active { background: var(--acento-suave); color: var(--acento); }
        .sidebar .nav-link.active i { color: var(--acento); }
        .sidebar-pie { flex: 0 0 auto; border-top: 1px solid var(--borde); padding: .5rem; }
        .btn-collapse-sidebar {
            display: flex; align-items: center; gap: .6rem; width: 100%; padding: .42rem .65rem; border: 0; background: transparent;
            color: var(--texto-3); font-size: .8rem; border-radius: var(--radio-sm);
        }
        .btn-collapse-sidebar:hover { background: var(--superficie-2); color: var(--texto); }
        .btn-collapse-sidebar i { width: 18px; text-align: center; }

        .content-wrap { margin-left: var(--sidebar-ancho); min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .2s ease; }

        .sidebar.collapsed { width: 60px; }
        .sidebar.collapsed .logo-box { justify-content: center; padding: 0; }
        .sidebar.collapsed .logo-box .marca,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-text { display: none; }
        .sidebar.collapsed .nav-link, .sidebar.collapsed .btn-collapse-sidebar { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed nav { padding-top: 1rem; }
        .content-wrap.collapsed { margin-left: 60px; }

        .sidebar-backdrop { display: none; }

        /* ---------------- Barra superior ---------------- */
        .topbar {
            height: 56px; padding: 0 1.5rem; background: var(--superficie); border-bottom: 1px solid var(--borde);
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        .topbar-titulo { font-size: 1rem; font-weight: 600; margin: 0; color: var(--texto); }
        .topbar-fecha { color: var(--texto-3); font-size: .8125rem; }
        .btn-topbar { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid transparent; background: transparent; border-radius: var(--radio-sm); color: var(--texto-2); position: relative; }
        .btn-topbar:hover { background: var(--superficie-2); border-color: var(--borde); color: var(--texto); }
        .btn-topbar .contador { position: absolute; top: 4px; right: 3px; min-width: 16px; height: 16px; padding: 0 4px; border-radius: 8px; background: var(--rojo); color: #fff; font-size: .65rem; font-weight: 600; line-height: 16px; text-align: center; }
        .usuario-menu { display: flex; align-items: center; gap: .6rem; padding: .25rem .5rem .25rem .25rem; border: 1px solid transparent; background: transparent; border-radius: var(--radio-sm); }
        .usuario-menu:hover, .usuario-menu[aria-expanded="true"] { background: var(--superficie-2); border-color: var(--borde); }
        .avatar { width: 30px; height: 30px; border-radius: 50%; background: var(--acento); color: #fff; font-size: .75rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .usuario-menu .nombre { font-size: .8125rem; font-weight: 500; color: var(--texto); line-height: 1.1; text-align: left; }
        .usuario-menu .rol { font-size: .72rem; color: var(--texto-3); line-height: 1.1; text-align: left; }
        .main-content { padding: 1.5rem; flex: 1; max-width: 1480px; width: 100%; }
        .toggler-mobile { display: none; }

        /* ---------------- Botones ---------------- */
        .btn { --bs-btn-font-size: .8125rem; --bs-btn-font-weight: 500; --bs-btn-padding-y: .44rem; --bs-btn-padding-x: .85rem; --bs-btn-border-radius: var(--radio-sm); box-shadow: none !important; transition: background-color .12s ease, border-color .12s ease, color .12s ease; }
        .btn-sm { --bs-btn-font-size: .78rem; --bs-btn-padding-y: .28rem; --bs-btn-padding-x: .6rem; }
        .btn-lg { --bs-btn-font-size: .9rem; --bs-btn-padding-y: .6rem; }
        .btn-morado, .btn-primary {
            --bs-btn-color: #fff; --bs-btn-bg: var(--acento); --bs-btn-border-color: var(--acento);
            --bs-btn-hover-color: #fff; --bs-btn-hover-bg: var(--acento-hover); --bs-btn-hover-border-color: var(--acento-hover);
            --bs-btn-active-color: #fff; --bs-btn-active-bg: var(--acento-hover); --bs-btn-active-border-color: var(--acento-hover);
            --bs-btn-disabled-bg: var(--acento); --bs-btn-disabled-border-color: var(--acento);
            background: var(--bs-btn-bg); color: var(--bs-btn-color); border: 1px solid var(--bs-btn-border-color);
        }
        .btn-morado:hover, .btn-morado:focus { background: var(--acento-hover); color: #fff; border-color: var(--acento-hover); }
        .btn-light, .btn-outline-secondary, .btn-volver, .btn-ir, .btn-outline-primary, .btn-outline-dark {
            --bs-btn-color: var(--texto); --bs-btn-bg: var(--superficie); --bs-btn-border-color: var(--borde-fuerte);
            --bs-btn-hover-color: var(--texto); --bs-btn-hover-bg: var(--superficie-2); --bs-btn-hover-border-color: var(--borde-fuerte);
            --bs-btn-active-color: var(--texto); --bs-btn-active-bg: #f0f0ec; --bs-btn-active-border-color: var(--borde-fuerte);
            background: var(--bs-btn-bg); color: var(--bs-btn-color); border: 1px solid var(--bs-btn-border-color);
        }
        .btn-light:hover, .btn-outline-secondary:hover, .btn-volver:hover, .btn-ir:hover, .btn-outline-primary:hover, .btn-outline-dark:hover { background: var(--superficie-2); color: var(--texto); border-color: var(--borde-fuerte); }
        .btn-ir { color: var(--acento); }
        .btn-ir:hover { color: var(--acento-hover); }
        .btn-success { --bs-btn-bg: var(--verde); --bs-btn-border-color: var(--verde); --bs-btn-hover-bg: #17653a; --bs-btn-hover-border-color: #17653a; }
        .btn-danger { --bs-btn-bg: var(--rojo); --bs-btn-border-color: var(--rojo); --bs-btn-hover-bg: #962016; --bs-btn-hover-border-color: #962016; }
        .btn-outline-danger { --bs-btn-color: var(--rojo); --bs-btn-border-color: #f1c6c1; --bs-btn-hover-bg: var(--rojo-suave); --bs-btn-hover-color: var(--rojo); --bs-btn-hover-border-color: #eab0a9; }
        .btn-outline-success { --bs-btn-color: var(--verde); --bs-btn-border-color: #bfe0cb; --bs-btn-hover-bg: var(--verde-suave); --bs-btn-hover-color: var(--verde); --bs-btn-hover-border-color: #a8d5b8; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--radio-sm); }
        .btn-close { opacity: .45; }
        .btn-close:hover { opacity: .8; }

        /* ---------------- Formularios ---------------- */
        .form-control, .form-select {
            font-size: .8125rem; padding: .45rem .7rem; min-height: 36px; border: 1px solid var(--borde-fuerte); border-radius: var(--radio-sm);
            background-color: var(--superficie); color: var(--texto);
        }
        .form-select { padding-right: 2rem; background-position: right .6rem center; background-size: 14px 10px; }
        .form-control-sm, .form-select-sm { min-height: 30px; padding: .25rem .55rem; font-size: .78rem; }
        .form-select-sm { padding-right: 1.8rem; }
        .form-control::placeholder { color: #a3a19a; }
        .form-control:focus, .form-select:focus { border-color: var(--acento); box-shadow: 0 0 0 3px rgba(61,44,141,.12); }
        .form-control-color { padding: .25rem; }
        .form-check-input { border-color: var(--borde-fuerte); }
        .form-check-input:checked { background-color: var(--acento); border-color: var(--acento); }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(61,44,141,.12); border-color: var(--acento); }
        .form-label { font-size: .8125rem; font-weight: 500; color: var(--texto-2); margin-bottom: .3rem; }
        .form-text { font-size: .75rem; color: var(--texto-3); }
        .input-group-text { font-size: .8125rem; background: var(--superficie-2); border-color: var(--borde-fuerte); color: var(--texto-2); }

        /* ---------------- Tarjetas ---------------- */
        .card { --bs-card-bg: var(--superficie); border: 1px solid var(--borde); border-radius: var(--radio); box-shadow: none; }
        .card-header { background: transparent; border-bottom: 1px solid var(--borde); font-weight: 600; padding: .75rem 1rem; }
        .card.p-3 > h6:first-child, .card.p-4 > h6:first-child { font-size: .875rem; font-weight: 600; }
        .card-kpi { border: 1px solid var(--borde); }
        .card-kpi .fs-3 { font-size: 1.625rem !important; font-weight: 600 !important; color: var(--texto); font-variant-numeric: tabular-nums; }
        .kpi-icon { width: 32px; height: 32px; border-radius: var(--radio-sm); display: inline-flex; align-items: center; justify-content: center; background: var(--superficie-2); border: 1px solid var(--borde); color: var(--texto-3); }
        .kpi-icon .fs-5 { font-size: 1rem !important; }
        a > .card { transition: border-color .12s ease; }
        a > .card:hover { border-color: var(--borde-fuerte); }
        .shadow-sm, .shadow { box-shadow: none !important; }

        /* ---------------- Tablas ---------------- */
        .table { --bs-table-bg: transparent; --bs-table-hover-bg: var(--superficie-2); margin-bottom: 0; font-size: .8125rem; font-variant-numeric: tabular-nums; }
        .table > :not(caption) > * > * { padding: .6rem .75rem; border-bottom-color: #efeeea; }
        .table td { vertical-align: middle; color: var(--texto); }
        .table thead th {
            background: var(--superficie-2); color: var(--texto-3); font-size: .75rem; font-weight: 500;
            text-transform: none; letter-spacing: 0; white-space: nowrap; border-bottom: 1px solid var(--borde); padding-top: .5rem; padding-bottom: .5rem;
        }
        .table tbody tr:last-child > * { border-bottom: 0; }
        .table tbody tr:hover > * { background: var(--superficie-2); }
        .table .fw-semibold { font-weight: 500 !important; }
        .table-sm > :not(caption) > * > * { padding: .4rem .6rem; }
        .card > .table-responsive, .card > div > .table-responsive { border: 1px solid var(--borde); border-radius: var(--radio-sm); }
        .card.p-3 > .table-responsive, .card.p-4 > .table-responsive { margin: 0; }
        .table td.text-end, .table th.text-end { white-space: nowrap; }
        .table tr.fila-actual > * { background: var(--acento-suave); }
        .table tr.fila-actual:hover > * { background: #ebe8f5; }

        /* Acciones de fila: botones sobrios con texto (el color solo marca lo destructivo). */
        .table .btn-icon {
            width: auto; height: 28px; padding: 0 .55rem; gap: .3rem; font-size: .75rem; font-weight: 500;
            background: var(--superficie); border: 1px solid var(--borde-fuerte); color: var(--texto-2);
        }
        .table .btn-icon i { font-size: .8rem; }
        .table .btn-icon:hover { background: var(--superficie-2); color: var(--texto); }
        .table .btn-icon:has(.bi-eye)::after { content: "Ver"; }
        .table .btn-icon:has(.bi-pencil)::after { content: "Editar"; }
        .table .btn-icon:has(.bi-trash)::after { content: "Eliminar"; }
        .table .btn-icon:has(.bi-cash-stack)::after { content: "Abonos"; }
        .table .btn-icon:has(.bi-file-earmark-pdf)::after { content: "PDF"; }
        .table .btn-icon:has(.bi-trash) { color: var(--rojo) !important; border-color: #f0cfcb; }
        .table .btn-icon:has(.bi-trash):hover { background: var(--rojo-suave); }
        .table .btn-icon:has(.bi-pencil) i, .table .btn-icon:has(.bi-eye) i, .table .btn-icon:has(.bi-file-earmark-pdf) i { color: var(--texto-3); }
        .table td:has(.btn-icon) { white-space: nowrap; }
        /* Dentro de una tabla, el boton principal se vuelve secundario para no repetir 20 botones de color. */
        .table .btn-morado, .table .btn-primary { background: var(--superficie); color: var(--acento); border-color: var(--borde-fuerte); }
        .table .btn-morado:hover, .table .btn-primary:hover { background: var(--acento-suave); color: var(--acento); border-color: #cfc8ea; }

        /* ---------------- Estados (badges) ---------------- */
        .badge { font-size: .72rem; font-weight: 500; letter-spacing: 0; border-radius: 4px; padding: .22rem .45rem; }
        .badge.bg-success, .badge.bg-danger, .badge.bg-warning, .badge.bg-secondary, .badge.bg-info, .badge.bg-primary, .badge.bg-dark { display: inline-flex; align-items: center; gap: .35rem; }
        .badge.bg-success::before, .badge.bg-danger::before, .badge.bg-warning::before, .badge.bg-secondary::before, .badge.bg-info::before, .badge.bg-primary::before {
            content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0;
        }
        .badge.bg-success { background: var(--verde-suave) !important; color: var(--verde) !important; }
        .badge.bg-danger { background: var(--rojo-suave) !important; color: var(--rojo) !important; }
        .badge.bg-warning { background: var(--ambar-suave) !important; color: var(--ambar) !important; }
        .badge.bg-secondary { background: #f0f0ec !important; color: var(--texto-2) !important; }
        .badge.bg-info { background: var(--azul-suave) !important; color: var(--azul) !important; }
        .badge.bg-primary { background: var(--acento-suave) !important; color: var(--acento) !important; }
        .badge.bg-dark { background: var(--texto) !important; color: #fff !important; }
        .badge.bg-light { background: var(--superficie-2) !important; color: var(--texto-2) !important; border: 1px solid var(--borde) !important; }
        .badge-rol-admin, .badge-rol-recepcion { background: var(--superficie-2); color: var(--texto-2); border: 1px solid var(--borde); }

        /* ---------------- Pestanas, paginacion, alertas ---------------- */
        .nav-tabs { border-bottom: 1px solid var(--borde); gap: 1.25rem; }
        .nav-tabs .nav-link { border: 0; border-bottom: 2px solid transparent; margin-bottom: -1px; padding: .55rem 0; color: var(--texto-3); font-weight: 500; background: transparent; }
        .nav-tabs .nav-link:hover { color: var(--texto); border-bottom-color: var(--borde-fuerte); }
        .nav-tabs .nav-link.active { color: var(--texto); border-bottom-color: var(--acento); background: transparent; }
        .nav-pills .nav-link { color: var(--texto-2); border-radius: var(--radio-sm); font-weight: 500; }
        .nav-pills .nav-link.active { background: var(--acento-suave); color: var(--acento); }
        .pagination { --bs-pagination-font-size: .8125rem; gap: 4px; margin: 1rem 0 0; }
        .page-link { border-radius: var(--radio-sm) !important; border-color: var(--borde); color: var(--texto-2); min-width: 32px; text-align: center; }
        .page-link:hover { background: var(--superficie-2); color: var(--texto); }
        .page-item.active .page-link { background: var(--texto); border-color: var(--texto); color: #fff; }
        .alert { border-radius: var(--radio); border: 1px solid; font-size: .8125rem; padding: .75rem 1rem; }
        .alert-warning { background: var(--ambar-suave); border-color: #f1dca8; color: #5f4200; }
        .alert-danger { background: var(--rojo-suave); border-color: #f3c7c1; color: #7a1a12; }
        .alert-success { background: var(--verde-suave); border-color: #c3e3cf; color: #145230; }
        .alert-info { background: var(--azul-suave); border-color: #c8dbf3; color: #163f73; }
        .alert .fs-3 { font-size: 1.25rem !important; }
        .alert .fs-5 { font-size: .9375rem !important; }
        .progress { background: #efeeea; border-radius: 3px; }

        /* ---------------- Ventanas (modales), menus, alertas SweetAlert ---------------- */
        .modal-content { border: 1px solid var(--borde); border-radius: 10px; box-shadow: 0 16px 48px rgba(20, 20, 18, .18); }
        .modal-header { background: var(--superficie) !important; color: var(--texto) !important; border-bottom: 1px solid var(--borde); padding: .9rem 1.25rem; }
        .modal-title { font-size: .9375rem; font-weight: 600; }
        .modal-title i { color: var(--texto-3); }
        .modal-body { padding: 1.25rem; }
        .modal-footer { border-top: 1px solid var(--borde); padding: .75rem 1.25rem; background: var(--superficie-2); border-radius: 0 0 10px 10px; }
        .modal-backdrop.show { opacity: .35; }
        .dropdown-menu { font-size: .8125rem; border: 1px solid var(--borde); border-radius: var(--radio); box-shadow: 0 8px 24px rgba(20, 20, 18, .1); padding: .3rem; }
        .dropdown-item { border-radius: 4px; padding: .4rem .6rem; }
        .dropdown-item:hover { background: var(--superficie-2); }
        .swal2-popup { font-family: inherit !important; border-radius: 10px !important; font-size: .875rem !important; padding: 1.5rem !important; }
        .swal2-title { font-size: 1.05rem !important; font-weight: 600 !important; color: var(--texto) !important; }
        .swal2-html-container { color: var(--texto-2) !important; font-size: .875rem !important; }
        .swal2-styled { border-radius: var(--radio-sm) !important; font-size: .8125rem !important; font-weight: 500 !important; padding: .5rem 1rem !important; box-shadow: none !important; }
        .swal2-styled.swal2-confirm { background: var(--acento) !important; }
        .swal2-styled.swal2-cancel { background: var(--superficie) !important; color: var(--texto) !important; border: 1px solid var(--borde-fuerte) !important; }
        .swal2-icon { transform: scale(.75); margin: .5rem auto .25rem !important; }
        .swal2-toast { box-shadow: 0 8px 24px rgba(20,20,18,.12) !important; border: 1px solid var(--borde) !important; }

        .aviso-flotante { background: var(--superficie); border: 1px solid var(--borde); border-left: 3px solid var(--texto-3); }
        .aviso-urgente { border-left-color: var(--rojo) !important; }
        .aviso-advertencia { border-left-color: var(--ambar) !important; }

        /* ---------------- Encabezados de pagina dentro de las vistas ---------------- */
        .main-content > .d-flex:first-child h4, .main-content > .d-flex:first-child h5 { font-weight: 600; }
        .main-content h5.fw-semibold, .main-content h4.fw-semibold, .main-content h4.fw-bold { font-size: 1.125rem; }

        /* ---------------- Tablero de horarios (cuadro por maestro) ---------------- */
        .tablero-maestro { border: 1px solid var(--borde); border-radius: var(--radio); overflow: hidden; margin-bottom: 1rem; background: var(--superficie); }
        .tablero-header { background: var(--superficie); color: var(--texto); padding: .7rem 1rem; display: flex; align-items: baseline; gap: .6rem; flex-wrap: wrap; border-bottom: 1px solid var(--borde); }
        .tablero-titulo { font-weight: 600; font-size: .875rem; }
        .tablero-sub { font-size: .75rem; color: var(--texto-3); }
        table.tablero-tabla { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        table.tablero-tabla th { background: var(--superficie-2); color: var(--texto-3); font-size: .72rem; font-weight: 500; text-transform: none; text-align: center; padding: .45rem .35rem; border-bottom: 1px solid var(--borde); white-space: nowrap; }
        table.tablero-tabla td { border: 0; border-bottom: 1px solid #efeeea; border-left: 1px solid #efeeea; padding: .4rem .45rem; font-size: .78rem; vertical-align: top; }
        table.tablero-tabla td.col-hora { background: var(--superficie-2); font-weight: 500; color: var(--texto-2); text-align: center; white-space: nowrap; width: 84px; border-left: 0; }
        table.tablero-tabla td.celda-ocupada { background: var(--acento-suave); }
        .alumno-celda { line-height: 1.3; }
        .alumno-celda + .alumno-celda { margin-top: 4px; padding-top: 4px; border-top: 1px dashed #d9d5ea; }
        .alumno-celda.inactivo { color: var(--rojo); text-decoration: line-through; opacity: .75; }
        .edad-celda { color: var(--texto-3); font-size: .9em; margin-left: 2px; }

        /* ---------------- Celular ---------------- */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); width: 264px !important; box-shadow: none; }
            .sidebar.show { transform: translateX(0); box-shadow: 0 0 40px rgba(20,20,18,.2); }
            .sidebar.collapsed .logo-box { justify-content: flex-start; padding: 0 1rem; }
            .sidebar.collapsed .logo-box .marca, .sidebar.collapsed .nav-section { display: block; }
            .sidebar.collapsed .nav-text { display: inline; }
            .sidebar.collapsed .nav-link { justify-content: flex-start; padding-left: .65rem; }
            .sidebar-pie { display: none; }
            .sidebar-backdrop.show { display: block; position: fixed; inset: 0; background: rgba(20,20,18,.3); z-index: 1025; }
            .content-wrap, .content-wrap.collapsed { margin-left: 0; }
            .toggler-mobile { display: inline-flex; }
            .topbar { padding: 0 .75rem; }
            .topbar-fecha { display: none; }
            .main-content { padding: 1rem; }
        }

        /* Celular: cada fila de tabla se muestra como tarjeta "Campo: valor". */
        @media (max-width: 767px) {
            table.tabla-tarjetas thead { display: none; }
            table.tabla-tarjetas, table.tabla-tarjetas tbody, table.tabla-tarjetas tr, table.tabla-tarjetas td { display: block; width: 100%; }
            table.tabla-tarjetas tbody tr { background: var(--superficie); border: 1px solid var(--borde); border-radius: var(--radio); margin-bottom: .6rem; padding: .55rem .8rem; }
            table.tabla-tarjetas tbody tr:hover > * { background: transparent; }
            table.tabla-tarjetas tbody tr:last-child > * { border-bottom: 0 !important; }
            table.tabla-tarjetas td { border: 0 !important; padding: .28rem 0 !important; }
            table.tabla-tarjetas td[data-label] { position: relative; padding-left: 42% !important; text-align: right !important; min-height: 1.8rem; }
            table.tabla-tarjetas td[data-label]::before { content: attr(data-label); position: absolute; left: 0; top: .3rem; width: 40%; text-align: left; font-size: .75rem; color: var(--texto-3); }
            table.tabla-tarjetas td.td-titulo .small { font-weight: 400; font-size: .78rem; }
            table.tabla-tarjetas td.td-titulo { padding-left: 0 !important; text-align: left !important; font-size: .9rem; font-weight: 600; padding-bottom: .45rem !important; margin-bottom: .2rem; border-bottom: 1px solid #efeeea !important; }
            table.tabla-tarjetas td.td-acciones { padding-left: 0 !important; text-align: left !important; white-space: normal; padding-top: .55rem !important; margin-top: .3rem; border-top: 1px solid #efeeea !important; }
            table.tabla-tarjetas td.td-acciones .btn { margin: 0 .25rem .25rem 0 !important; }
            .table-responsive:has(> table.tabla-tarjetas) { border: 0 !important; }
            .card:has(table.tabla-tarjetas) { background: transparent; border: 0; padding: 0 !important; }
        }

        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-thumb { background: #d6d5cf; border-radius: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
    </style>
    @stack('estilos')
</head>
<body>

@php
    $usuarioActual = auth()->user();
    $iniciales = collect(preg_split('/\s+/', trim($usuarioActual->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'setiembre', 'octubre', 'noviembre', 'diciembre'];
    $dias = [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
@endphp

<aside class="sidebar" id="sidebar">
    <div class="logo-box">
        <img src="{{ asset('images/logo.png') }}" alt="">
        <div class="marca">
            <strong>MusicArte</strong>
            <span>Centro Cultural</span>
        </div>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Inicio"><i class="bi bi-house"></i> <span class="nav-text">Inicio</span></a>

        <div class="nav-section">Académico</div>
        <a class="nav-link {{ request()->routeIs('alumnos.*') || request()->routeIs('periodos.*') ? 'active' : '' }}" href="{{ route('alumnos.index') }}" title="Alumnos"><i class="bi bi-people"></i> <span class="nav-text">Alumnos</span></a>
        <a class="nav-link {{ request()->routeIs('maestros.*') ? 'active' : '' }}" href="{{ route('maestros.index') }}" title="Maestros"><i class="bi bi-person-badge"></i> <span class="nav-text">Maestros</span></a>
        <a class="nav-link {{ request()->routeIs('especialidades.*') ? 'active' : '' }}" href="{{ route('especialidades.index') }}" title="Especialidades"><i class="bi bi-music-note-beamed"></i> <span class="nav-text">Especialidades</span></a>

        <div class="nav-section">Clases</div>
        <a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}" title="Calendario"><i class="bi bi-calendar3"></i> <span class="nav-text">Calendario</span></a>
        <a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.index') }}" title="Horarios"><i class="bi bi-clock"></i> <span class="nav-text">Horarios</span></a>
        <a class="nav-link {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.index') }}" title="Asistencia"><i class="bi bi-check2-square"></i> <span class="nav-text">Asistencia</span></a>
        <a class="nav-link {{ request()->routeIs('recitales.*') ? 'active' : '' }}" href="{{ route('recitales.index') }}" title="Recitales y eventos"><i class="bi bi-mic"></i> <span class="nav-text">Recitales y eventos</span></a>

        <div class="nav-section">Administración</div>
        <a class="nav-link {{ request()->routeIs('pagos.*') ? 'active' : '' }}" href="{{ route('pagos.index') }}" title="Pagos"><i class="bi bi-credit-card"></i> <span class="nav-text">Pagos</span></a>
        <a class="nav-link {{ request()->routeIs('egresos.*') ? 'active' : '' }}" href="{{ route('egresos.index') }}" title="Egresos"><i class="bi bi-receipt"></i> <span class="nav-text">Egresos</span></a>
        <a class="nav-link {{ request()->routeIs('caja-chica.*') ? 'active' : '' }}" href="{{ route('caja-chica.index') }}" title="Caja chica"><i class="bi bi-wallet2"></i> <span class="nav-text">Caja chica</span></a>
        <a class="nav-link {{ request()->routeIs('planilla.*') ? 'active' : '' }}" href="{{ route('planilla.index') }}" title="Planilla de maestros"><i class="bi bi-file-earmark-text"></i> <span class="nav-text">Planilla de maestros</span></a>

        <div class="nav-section">General</div>
        <a class="nav-link {{ request()->routeIs('avisos.*') ? 'active' : '' }}" href="{{ route('avisos.index') }}" title="Avisos"><i class="bi bi-megaphone"></i> <span class="nav-text">Avisos</span></a>
        <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}" title="Reportes"><i class="bi bi-bar-chart"></i> <span class="nav-text">Reportes</span></a>
    </nav>
    <div class="sidebar-pie">
        <button class="btn-collapse-sidebar" id="btnCollapseSidebar" title="Contraer menú">
            <i class="bi bi-layout-sidebar" id="iconCollapseSidebar"></i> <span class="nav-text">Contraer menú</span>
        </button>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="content-wrap" id="contentWrap">
    <header class="topbar">
        <div class="d-flex align-items-center gap-2 min-w-0">
            <button class="btn-topbar toggler-mobile" id="btnToggleSidebar" aria-label="Abrir menú"><i class="bi bi-list fs-5"></i></button>
            <h1 class="topbar-titulo text-truncate">@yield('titulo', 'Panel')</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="topbar-fecha me-2">{{ ucfirst($dias[now()->isoWeekday()]) }}, {{ now()->day }} de {{ $meses[now()->month] }}</span>
            <button class="btn-topbar" id="btnAvisos" title="Avisos" aria-label="Avisos">
                <i class="bi bi-bell"></i>
                @if(($avisosFlotantes ?? collect())->count() > 0)
                    <span class="contador">{{ $avisosFlotantes->count() }}</span>
                @endif
            </button>
            <div class="dropdown">
                <button class="usuario-menu" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ $iniciales }}</span>
                    <span class="d-none d-sm-block">
                        <span class="nombre d-block">{{ $usuarioActual->name }}</span>
                        <span class="rol d-block">{{ $usuarioActual->rolLabel() }}</span>
                    </span>
                    <i class="bi bi-chevron-down small text-muted d-none d-sm-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-2 py-1 d-sm-none">
                        <div class="fw-semibold">{{ $usuarioActual->name }}</div>
                        <div class="small text-muted">{{ $usuarioActual->rolLabel() }}</div>
                    </li>
                    <li class="d-sm-none"><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2 text-muted"></i>Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="main-content">
        @yield('contenido')
    </main>
</div>

<!-- ===================== MODAL VENTANA FLOTANTE DE AVISOS ===================== -->
<div class="modal fade" id="modalAvisos" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Avisos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
            html: `Vas a eliminar <b>${nombre}</b>. Esta acción no se puede deshacer.`,
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#b42318',
        }).then(r => r.isConfirmed);
    }

    async function descartarAviso(id, btn) {
        await maFetch(`/avisos/${id}/descartar`, { method: 'POST' });
        btn.closest('.aviso-flotante').remove();
    }

    document.getElementById('btnAvisos')?.addEventListener('click', () => {
        new bootstrap.Modal('#modalAvisos').show();
    });

    // Menu en celular: se abre sobre el contenido con un fondo que lo cierra al tocarlo.
    function maMenuMovil(abrir) {
        document.getElementById('sidebar').classList.toggle('show', abrir);
        document.getElementById('sidebarBackdrop').classList.toggle('show', abrir);
    }
    document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
        maMenuMovil(!document.getElementById('sidebar').classList.contains('show'));
    });
    document.getElementById('sidebarBackdrop')?.addEventListener('click', () => maMenuMovil(false));

    // Menu contraido (solo iconos) en computadora; se recuerda entre visitas.
    function maMenuContraido(contraer) {
        document.getElementById('sidebar').classList.toggle('collapsed', contraer);
        document.getElementById('contentWrap')?.classList.toggle('collapsed', contraer);
        const btn = document.getElementById('btnCollapseSidebar');
        btn.title = contraer ? 'Expandir menú' : 'Contraer menú';
        btn.querySelector('.nav-text').textContent = btn.title;
    }
    document.getElementById('btnCollapseSidebar')?.addEventListener('click', () => {
        const contraer = !document.getElementById('sidebar').classList.contains('collapsed');
        maMenuContraido(contraer);
        try { localStorage.setItem('ma_sidebar_collapsed', contraer ? '1' : '0'); } catch (e) {}
    });
    try {
        if (localStorage.getItem('ma_sidebar_collapsed') === '1' && window.innerWidth >= 992) maMenuContraido(true);
    } catch (e) {}

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