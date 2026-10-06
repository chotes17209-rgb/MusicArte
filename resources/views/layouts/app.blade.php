@if(request()->header('X-Modal'))
{{-- Pedido desde la ventana "Ver": solo el contenido de la pantalla, sin menu ni barra. --}}
<div class="vista-fragmento" data-titulo="@yield('titulo', '')">
    @stack('estilos')
    @yield('contenido')
</div>
@else
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Panel') · MusicArte</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#800080">
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
            --acento: #800080;
            --acento-hover: #660066;
            --acento-suave: #f7ecf7;
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

        /* ---------------- Barra lateral (oscura, plana) ---------------- */
        .sidebar {
            width: var(--sidebar-ancho); height: 100vh; position: fixed; top: 0; left: 0; z-index: 1030;
            display: flex; flex-direction: column;
            background: var(--acento); border-right: 1px solid var(--acento-hover);
            transition: transform .2s ease, width .2s ease;
        }
        .sidebar .logo-box { display: flex; align-items: center; gap: .75rem; padding: 0 1rem; height: 64px; border-bottom: 1px solid rgba(255,255,255,.16); flex: 0 0 auto; text-decoration: none; }
        .sidebar .logo-box img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; background: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,.12), 0 4px 14px rgba(0,0,0,.35); flex-shrink: 0; transition: width .2s ease, height .2s ease; }
        .sidebar .logo-box .marca { line-height: 1.15; min-width: 0; }
        .sidebar .logo-box .marca strong { display: block; font-size: 1.1rem; font-weight: 600; color: #fff; letter-spacing: -.01em; }
        .sidebar .logo-box .marca span { display: block; font-size: .72rem; color: rgba(255,255,255,.7); }
        .sidebar nav { flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; flex-wrap: nowrap; padding: .5rem .6rem 1rem; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.3) transparent; }
        .sidebar .nav-section { font-size: .68rem; font-weight: 600; color: rgba(255,255,255,.62); padding: 1.1rem .65rem .35rem; text-transform: uppercase; letter-spacing: .06em; }
        .sidebar .nav-link {
            display: flex; align-items: center; gap: .65rem; padding: .45rem .65rem; margin: 1px 0; position: relative;
            color: rgba(255,255,255,.88); font-size: .85rem; font-weight: 500; border-radius: var(--radio-sm); white-space: nowrap;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 18px; text-align: center; color: rgba(255,255,255,.7); flex-shrink: 0; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.12); color: #fff; }
        .sidebar .nav-link:hover i { color: #fff; }
        .sidebar .nav-link.active { background: rgba(255,255,255,.2); color: #fff; font-weight: 600; }
        .sidebar .nav-link.active i { color: #fff; }
        .sidebar .nav-link.active::before { content: ""; position: absolute; left: -.6rem; top: 7px; bottom: 7px; width: 3px; border-radius: 0 3px 3px 0; background: #fff; }
        .sidebar-pie { flex: 0 0 auto; border-top: 1px solid rgba(255,255,255,.16); padding: .5rem .6rem; }
        .btn-collapse-sidebar {
            display: flex; align-items: center; gap: .65rem; width: 100%; padding: .45rem .65rem; border: 0; background: transparent;
            color: rgba(255,255,255,.75); font-size: .8rem; border-radius: var(--radio-sm);
        }
        .btn-collapse-sidebar:hover { background: rgba(255,255,255,.12); color: #fff; }
        .btn-collapse-sidebar i { width: 18px; text-align: center; }

        .content-wrap { margin-left: var(--sidebar-ancho); min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .2s ease; }

        .sidebar.collapsed { width: 60px; }
        .sidebar.collapsed .logo-box { justify-content: center; padding: 0; }
        .sidebar.collapsed .logo-box img { width: 36px; height: 36px; }
        .sidebar.collapsed .logo-box .marca,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-text { display: none; }
        .sidebar.collapsed .nav-link, .sidebar.collapsed .btn-collapse-sidebar { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar.collapsed nav { padding-top: 1rem; }
        .content-wrap.collapsed { margin-left: 60px; }

        .sidebar-backdrop { display: none; }

        /* ---------------- Barra superior ---------------- */
        .topbar {
            height: 64px; padding: 0 1.5rem; background: var(--superficie); border-bottom: 1px solid var(--borde);
            display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        .logo-movil { display: none; flex-shrink: 0; }
        .logo-movil img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; box-shadow: 0 0 0 1px var(--borde); }
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
        .form-control:focus, .form-select:focus { border-color: var(--acento); box-shadow: 0 0 0 3px rgba(128,0,128,.12); }
        .form-control-color { padding: .25rem; }
        .form-check-input { border-color: var(--borde-fuerte); }
        .form-check-input:checked { background-color: var(--acento); border-color: var(--acento); }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(128,0,128,.12); border-color: var(--acento); }
        .form-label { font-size: .8125rem; font-weight: 500; color: var(--texto-2); margin-bottom: .3rem; }
        .form-text { font-size: .75rem; color: var(--texto-3); }
        .input-group-text { font-size: .8125rem; background: var(--superficie-2); border-color: var(--borde-fuerte); color: var(--texto-2); }

        /* ---------------- Tarjetas ---------------- */
        .card { --bs-card-bg: var(--superficie); border: 1px solid var(--borde); border-radius: var(--radio); box-shadow: 0 1px 2px rgba(20, 20, 18, .04); }
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
        .table .btn-morado:hover, .table .btn-primary:hover { background: var(--acento-suave); color: var(--acento); border-color: #e0b8e0; }

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

        /* ---------------- Encabezado de pagina, indicadores y barras ---------------- */
        .page-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .page-head h4 { font-size: 1.25rem; font-weight: 600; }
        .page-head .text-muted { max-width: 640px; }
        .page-head-acciones { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
        .page-head-acciones form { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; margin: 0; }
        .page-head-acciones .form-select, .page-head-acciones .form-control { width: auto; min-width: 120px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
        @media (max-width: 575px) { .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; } .stat { padding: .7rem .8rem; } .stat-valor { font-size: 1.25rem; } .page-head h4 { font-size: 1.1rem; } }
        .stat { background: var(--superficie); border: 1px solid var(--borde); border-radius: var(--radio); padding: .9rem 1rem; box-shadow: 0 1px 2px rgba(20, 20, 18, .04); }
        .min-w-0 { min-width: 0; }
        .stat-label { font-size: .78rem; color: var(--texto-3); margin-bottom: .2rem; }
        .stat-valor { font-size: 1.5rem; font-weight: 600; letter-spacing: -.01em; font-variant-numeric: tabular-nums; line-height: 1.2; color: var(--texto); }
        .stat-detalle { font-size: .78rem; color: var(--texto-3); margin-top: .2rem; }
        .tono-verde { color: var(--verde) !important; }
        .tono-rojo { color: var(--rojo) !important; }
        .tono-ambar { color: var(--ambar) !important; }
        .tono-acento { color: var(--acento) !important; }
        .barra-fila { display: flex; align-items: center; gap: .6rem; min-width: 140px; }
        .barra { flex: 1; height: 6px; background: #efeeea; border-radius: 3px; overflow: hidden; }
        .barra > span { display: block; height: 100%; border-radius: 3px; background: var(--acento); }
        .barra > span.tono-verde { background: var(--verde); }
        .barra > span.tono-rojo { background: var(--rojo); }
        .barra > span.tono-ambar { background: #d49a1a; }
        .barra > span.tono-acento { background: var(--acento); }
        .barra-num { font-size: .78rem; color: var(--texto-2); min-width: 42px; text-align: right; font-variant-numeric: tabular-nums; }
        .seccion-titulo { font-size: .875rem; font-weight: 600; margin: 0 0 .75rem; }
        .table tfoot td { font-weight: 600; background: var(--superficie-2); border-top: 1px solid var(--borde); }
        .vacio { text-align: center; padding: 2.5rem 1rem; color: var(--texto-3); }
        .vacio i { font-size: 1.5rem; display: block; margin-bottom: .5rem; color: var(--borde-fuerte); }
        @media print {
            .sidebar, .topbar, .page-head-acciones, .btn-volver, .sidebar-backdrop { display: none !important; }
            .content-wrap { margin-left: 0 !important; }
            .main-content { padding: 0 !important; }
            body { background: #fff; }
        }

        /* Chips neutros (ej. especialidades de un maestro) */
        .chip { display: inline-flex; align-items: center; gap: .35rem; padding: .18rem .5rem; margin: 0 .25rem .25rem 0; border: 1px solid var(--borde); border-radius: 999px; font-size: .75rem; background: var(--superficie); white-space: nowrap; }
        .chip-punto { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        /* Historial por mes: cuadritos tipo calendario */
        .celda-mes { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 22px; border-radius: 5px; font-size: .78rem; vertical-align: middle; }
        .celda-mes.activa { background: var(--verde-suave); color: var(--verde); }
        .celda-mes.inactiva { background: #f0f0ec; color: var(--texto-3); }
        .celda-mes.pasada { background: #f0f0ec; color: var(--texto-2); }
        .celda-mes.vacia { border: 1px dashed var(--borde); }
        table.historial td, table.historial th { text-align: center; }
        table.historial td:first-child, table.historial th:first-child { text-align: left; }

        /* ---------------- Barra superior: buscador, periodo, paneles ---------------- */
        .topbar { gap: 1rem; }
        .buscador { position: relative; flex: 1 1 auto; max-width: 420px; margin: 0 auto 0 1rem; }
        .buscador input { width: 100%; height: 36px; padding: 0 4.5rem 0 2.2rem; border: 1px solid var(--borde); border-radius: 8px; background: var(--superficie-2); font-size: .8125rem; color: var(--texto); transition: border-color .12s, background .12s, box-shadow .12s; }
        .buscador input:focus { outline: 0; background: var(--superficie); border-color: var(--acento); box-shadow: 0 0 0 3px rgba(128,0,128,.12); }
        .buscador input::-webkit-search-cancel-button { display: none; }
        .buscador-icono { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: var(--texto-3); font-size: .85rem; pointer-events: none; }
        .buscador-atajo { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); font-size: .68rem; font-family: inherit; color: var(--texto-3); background: var(--superficie); border: 1px solid var(--borde); border-radius: 4px; padding: .1rem .35rem; pointer-events: none; }
        .buscador-resultados { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: var(--superficie); border: 1px solid var(--borde); border-radius: 10px; box-shadow: 0 16px 40px rgba(20,20,18,.14); padding: .35rem; max-height: 420px; overflow-y: auto; z-index: 1050; }
        .resultado-grupo { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--texto-3); padding: .5rem .6rem .25rem; }
        .resultado { display: flex; align-items: center; gap: .6rem; padding: .45rem .6rem; border-radius: 6px; color: var(--texto); }
        .resultado.activo, .resultado:hover { background: var(--acento-suave); color: var(--texto); }
        .resultado-icono { width: 28px; height: 28px; border-radius: 6px; background: var(--superficie-2); border: 1px solid var(--borde); display: inline-flex; align-items: center; justify-content: center; color: var(--texto-3); flex-shrink: 0; }
        .periodo-pill { display: inline-flex; align-items: center; gap: .45rem; height: 36px; padding: 0 .75rem; border: 1px solid var(--borde); border-radius: 8px; background: var(--superficie); color: var(--texto); font-size: .8125rem; font-weight: 500; white-space: nowrap; }
        .periodo-pill:hover, .periodo-pill[aria-expanded="true"] { background: var(--superficie-2); border-color: var(--borde-fuerte); }
        .periodo-pill > .bi-calendar-range { color: var(--acento); }
        .punto-vivo { width: 7px; height: 7px; border-radius: 50%; background: var(--verde); box-shadow: 0 0 0 3px var(--verde-suave); }
        .panel-menu { width: 340px; max-width: calc(100vw - 1.5rem); padding: 0; overflow: hidden; }
        .panel-menu-cab { padding: .75rem 1rem; border-bottom: 1px solid var(--borde); }
        .panel-menu-cab strong { display: block; font-size: .875rem; }
        .panel-menu-cab span { display: block; font-size: .75rem; color: var(--texto-3); margin-top: .1rem; }
        .panel-menu-lista { max-height: 360px; overflow-y: auto; padding: .35rem; margin: 0; }
        .panel-menu-pie { padding: .5rem; border-top: 1px solid var(--borde); background: var(--superficie-2); margin: 0; }
        .panel-item { display: flex; align-items: center; gap: .6rem; width: 100%; padding: .5rem .65rem; border: 0; background: transparent; border-radius: 6px; font-size: .8125rem; color: var(--texto); }
        .panel-item:hover { background: var(--superficie-2); }
        .panel-item.activo { background: var(--acento-suave); }
        .aviso-item { display: flex; gap: .65rem; align-items: flex-start; padding: .6rem .65rem; border-radius: 6px; font-size: .8125rem; }
        .aviso-item:hover { background: var(--superficie-2); }
        .aviso-item .btn-close { font-size: .6rem; margin-top: .2rem; flex-shrink: 0; }
        .aviso-texto { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .aviso-icono { width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .tono-fondo-rojo { background: var(--rojo-suave); color: var(--rojo); }
        .tono-fondo-ambar { background: var(--ambar-suave); color: var(--ambar); }
        .tono-fondo-azul { background: var(--azul-suave); color: var(--azul); }
        .tono-fondo-verde { background: var(--verde-suave); color: var(--verde); }
        @media (max-width: 767px) {
            .buscador { display: none; position: fixed; top: 8px; left: 8px; right: 8px; max-width: none; margin: 0; z-index: 1060; }
            .buscador.abierto { display: block; }
            .buscador.abierto input { height: 42px; background: var(--superficie); box-shadow: 0 8px 30px rgba(20,20,18,.2); }
            .buscador-atajo { display: none; }
            .topbar { gap: .5rem; }
        }

        /* ---------------- Notificaciones (toasts) ---------------- */
        .toasts { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 2000; display: flex; flex-direction: column; gap: .5rem; width: 360px; max-width: calc(100vw - 2rem); pointer-events: none; }
        .toast-ma { position: relative; overflow: hidden; pointer-events: auto; display: flex; align-items: flex-start; gap: .7rem; padding: .8rem .9rem .9rem; background: var(--superficie); border: 1px solid var(--borde); border-radius: 10px; box-shadow: 0 12px 32px rgba(20,20,18,.14); animation: toastEntra .22s ease-out; }
        .toast-ma.saliendo { animation: toastSale .2s ease-in forwards; }
        .toast-icono { font-size: 1.1rem; line-height: 1.3; }
        .toast-success .toast-icono { color: var(--verde); }
        .toast-error .toast-icono { color: var(--rojo); }
        .toast-warning .toast-icono { color: #d49a1a; }
        .toast-info .toast-icono { color: var(--azul); }
        .toast-texto { flex: 1; min-width: 0; font-size: .8125rem; line-height: 1.4; }
        .toast-texto strong { display: block; font-weight: 600; color: var(--texto); }
        .toast-texto span { color: var(--texto-2); }
        .toast-cerrar { border: 0; background: transparent; color: var(--texto-3); padding: 0; line-height: 1; font-size: 1.1rem; }
        .toast-cerrar:hover { color: var(--texto); }
        .toast-progreso { position: absolute; left: 0; bottom: 0; height: 2px; width: 100%; transform-origin: left; animation: toastTiempo linear forwards; background: currentColor; opacity: .5; }
        .toast-success .toast-progreso { color: var(--verde); }
        .toast-error .toast-progreso { color: var(--rojo); }
        .toast-warning .toast-progreso { color: #d49a1a; }
        .toast-info .toast-progreso { color: var(--azul); }
        .toast-ma.pausado .toast-progreso { animation-play-state: paused; }
        @keyframes toastEntra { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }
        @keyframes toastSale { to { opacity: 0; transform: translateX(16px); } }
        @keyframes toastTiempo { from { transform: scaleX(1); } to { transform: scaleX(0); } }
        @media (max-width: 575px) { .toasts { left: 1rem; right: 1rem; bottom: 1rem; width: auto; } }

        /* ---------------- Ventana "Ver" ---------------- */
        #modalVista .modal-body { background: var(--fondo); padding: 1.25rem; }
        #modalVista .btn-volver { display: none; }
        #modalVista .page-head { margin-bottom: 1rem; }
        .cargando { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: 3rem 1rem; color: var(--texto-3); }

        /* Ficha de datos (pantallas "Ver") */
        .ficha { display: grid; grid-template-columns: minmax(120px, 34%) 1fr; gap: .55rem 1rem; margin: 0; font-size: .8125rem; }
        .ficha dt { color: var(--texto-3); font-weight: 500; }
        .ficha dd { margin: 0; color: var(--texto); min-width: 0; overflow-wrap: anywhere; }

        /* ---------------- Cuadro de horarios por maestro (como la hoja del salon) ---------------- */
        .cuadros { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 540px), 1fr)); gap: 1rem; align-items: start; }
        .cuadro { background: var(--superficie); border: 1px solid var(--borde); border-radius: var(--radio); overflow: hidden; box-shadow: 0 1px 2px rgba(20,20,18,.04); }
        .cuadro-cab { display: flex; align-items: baseline; gap: .6rem; flex-wrap: wrap; padding: .7rem 1rem; border-bottom: 1px solid var(--borde); border-left: 3px solid var(--acento); }
        .cuadro-titulo { font-weight: 600; font-size: .95rem; }
        .cuadro-sub { font-size: .78rem; color: var(--texto-3); }
        .cuadro-cuenta { margin-left: auto; font-size: .75rem; color: var(--texto-3); }
        .cuadro-tabla { width: 100%; border-collapse: collapse; font-size: .78rem; }
        .cuadro-tabla th { background: var(--superficie-2); color: var(--texto-2); font-weight: 600; font-size: .72rem; text-align: center; padding: .45rem .35rem; border-bottom: 1px solid var(--borde); border-left: 1px solid #efeeea; white-space: nowrap; }
        .cuadro-tabla td { vertical-align: top; padding: .35rem .4rem; border-top: 1px solid #efeeea; border-left: 1px solid #efeeea; min-width: 96px; }
        .cuadro-tabla td.vacia { background: #fcfcfa; }
        .cuadro-tabla .cuadro-hora { width: 70px; min-width: 70px; text-align: center; font-weight: 600; color: var(--texto-2); background: var(--superficie-2); border-left: 0; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .cuadro-alumno { display: flex; align-items: baseline; gap: .25rem; flex-wrap: wrap; padding: .12rem .2rem; margin: 0 -.2rem; border-radius: 4px; color: var(--texto); line-height: 1.3; }
        .cuadro-alumno:hover { background: var(--acento-suave); color: var(--texto); }
        .cuadro-alumno .punto { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; align-self: center; }
        .cuadro-alumno .nombre { font-weight: 500; }
        .cuadro-alumno .edad { color: var(--texto-3); }
        .cuadro-alumno .inst { color: var(--texto-3); font-size: .9em; font-style: italic; }
        .cuadro-alumno.inactivo { text-decoration: line-through; opacity: .6; }

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
            .toggler-mobile, .logo-movil { display: inline-flex; }
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
    <a class="logo-box" href="{{ route('dashboard') }}" title="Ir al inicio">
        <img src="{{ asset('images/logo.png') }}" alt="MusicArte">
        <div class="marca">
            <strong>MusicArte</strong>
            <span>Centro Cultural</span>
        </div>
    </a>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Inicio"><i class="bi bi-house"></i> <span class="nav-text">Inicio</span></a>

        <div class="nav-section">Académico</div>
        <a class="nav-link {{ request()->routeIs('alumnos.*') || request()->routeIs('periodos.*') ? 'active' : '' }}" href="{{ route('alumnos.index') }}" title="Alumnos"><i class="bi bi-people"></i> <span class="nav-text">Alumnos</span></a>
        <a class="nav-link {{ request()->routeIs('maestros.*') ? 'active' : '' }}" href="{{ route('maestros.index') }}" title="Maestros"><i class="bi bi-person-badge"></i> <span class="nav-text">Maestros</span></a>
        <a class="nav-link {{ request()->routeIs('especialidades.*') ? 'active' : '' }}" href="{{ route('especialidades.index') }}" title="Especialidades"><i class="bi bi-music-note-beamed"></i> <span class="nav-text">Especialidades</span></a>

        <div class="nav-section">Clases</div>
        <a class="nav-link {{ request()->routeIs('calendario.*') ? 'active' : '' }}" href="{{ route('calendario.index') }}" title="Calendario"><i class="bi bi-calendar3"></i> <span class="nav-text">Calendario</span></a>
        <a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.tablero') }}" title="Horarios"><i class="bi bi-clock"></i> <span class="nav-text">Horarios</span></a>
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
        @if($usuarioActual->esAdmin())
        <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}" title="Usuarios"><i class="bi bi-person-gear"></i> <span class="nav-text">Usuarios</span></a>
        @endif
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
            <a href="{{ route('dashboard') }}" class="logo-movil"><img src="{{ asset('images/logo.png') }}" alt="MusicArte"></a>
            <h1 class="topbar-titulo text-truncate">@yield('titulo', 'Panel')</h1>
        </div>

        {{-- Buscador rapido de alumnos y maestros (Ctrl + K) --}}
        <div class="buscador" id="buscador">
            <i class="bi bi-search buscador-icono"></i>
            <input type="search" id="buscadorInput" placeholder="Buscar alumno o maestro…" autocomplete="off" aria-label="Buscar">
            <kbd class="buscador-atajo">Ctrl K</kbd>
            <div class="buscador-resultados" id="buscadorResultados" hidden></div>
        </div>

        <div class="d-flex align-items-center gap-1 gap-sm-2 flex-shrink-0">
            <button class="btn-topbar d-md-none" id="btnBuscarMovil" aria-label="Buscar"><i class="bi bi-search"></i></button>

            {{-- Periodo de trabajo: todas las pantallas filtran por el por defecto --}}
            @if($periodoTrabajo)
            <div class="dropdown">
                <button class="periodo-pill" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Periodo con el que estás trabajando">
                    <i class="bi bi-calendar-range"></i>
                    <span class="d-none d-sm-inline">{{ $periodoTrabajo->nombre }}</span>
                    <span class="d-sm-none">{{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::before($periodoTrabajo->nombre, ' '), 3, '') }}</span>
                    @if($periodoTrabajo->estaEnCurso())<span class="punto-vivo" title="En curso"></span>@endif
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end panel-menu">
                    <div class="panel-menu-cab">
                        <strong>Periodo de trabajo</strong>
                        <span>Alumnos, pagos, horarios y reportes se muestran de este periodo.</span>
                    </div>
                    <form method="POST" action="{{ route('periodos.seleccionar') }}" class="panel-menu-lista">
                        @csrf
                        @foreach($periodosLista as $p)
                            <button type="submit" name="periodo_id" value="{{ $p->id }}" class="panel-item {{ $periodoTrabajo->id === $p->id ? 'activo' : '' }}">
                                <span class="flex-grow-1 text-start">
                                    {{ $p->nombre }}
                                    <span class="d-block small text-muted">{{ $p->fecha_inicio->format('d/m') }} al {{ $p->fecha_fin->format('d/m') }}</span>
                                </span>
                                @if($p->estaEnCurso())<span class="badge bg-success">En curso</span>@endif
                                @if($periodoTrabajo->id === $p->id)<i class="bi bi-check2 text-primary"></i>@endif
                            </button>
                        @endforeach
                    </form>
                    @if($periodoElegidoAMano)
                        <form method="POST" action="{{ route('periodos.seleccionar') }}" class="panel-menu-pie">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Volver al periodo en curso</button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            {{-- Avisos --}}
            <div class="dropdown">
                <button class="btn-topbar" id="btnAvisos" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Avisos" aria-label="Avisos">
                    <i class="bi bi-bell"></i>
                    @if(($avisosFlotantes ?? collect())->count() > 0)
                        <span class="contador" id="contadorAvisos">{{ $avisosFlotantes->count() }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end panel-menu panel-avisos">
                    <div class="panel-menu-cab d-flex justify-content-between align-items-center">
                        <strong>Avisos</strong>
                        <a href="{{ route('avisos.index') }}" class="small">Ver todos</a>
                    </div>
                    <div class="panel-menu-lista" id="listaAvisos">
                        @forelse(($avisosFlotantes ?? collect()) as $aviso)
                            @php $ic = ['urgente' => ['bi-exclamation-octagon', 'rojo'], 'advertencia' => ['bi-exclamation-triangle', 'ambar']][$aviso->tipo] ?? ['bi-info-circle', 'azul']; @endphp
                            <div class="aviso-item" data-aviso-id="{{ $aviso->id }}">
                                <span class="aviso-icono tono-fondo-{{ $ic[1] }}"><i class="bi {{ $ic[0] }}"></i></span>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-semibold">{{ $aviso->titulo }}</div>
                                    <div class="small text-muted aviso-texto">{!! nl2br(e($aviso->mensaje)) !!}</div>
                                </div>
                                <button class="btn-close" title="Descartar" onclick="descartarAviso({{ $aviso->id }}, this)"></button>
                            </div>
                        @empty
                            <div class="vacio py-4"><i class="bi bi-bell-slash"></i>No hay avisos por ahora.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <button class="usuario-menu" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ $iniciales }}</span>
                    <span class="d-none d-xl-block">
                        <span class="nombre d-block">{{ $usuarioActual->name }}</span>
                        <span class="rol d-block">{{ $usuarioActual->rolLabel() }}</span>
                    </span>
                    <i class="bi bi-chevron-down small text-muted d-none d-xl-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-2 py-1">
                        <div class="fw-semibold">{{ $usuarioActual->name }}</div>
                        <div class="small text-muted">{{ $usuarioActual->rolLabel() }} · {{ $usuarioActual->email }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
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

<!-- Notificaciones (toasts) -->
<div class="toasts" id="maToasts" aria-live="polite"></div>

<!-- Ventana para "Ver" (perfil de alumno, maestro, etc.) sin salir de la pantalla -->
<div class="modal fade" id="modalVista" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-topbar me-1" id="vistaAtras" title="Atrás" hidden><i class="bi bi-arrow-left"></i></button>
                <h5 class="modal-title text-truncate" id="vistaTitulo">Cargando…</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="vistaCuerpo"></div>
        </div>
    </div>
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
    // ---------- Notificaciones (toasts) ----------
    // maToast('success' | 'error' | 'warning' | 'info', 'Mensaje', 'Titulo opcional')
    const _TOAST_TIPOS = {
        success: { icono: 'bi-check-circle-fill', titulo: 'Listo' },
        error: { icono: 'bi-x-circle-fill', titulo: 'No se pudo completar' },
        warning: { icono: 'bi-exclamation-triangle-fill', titulo: 'Atención' },
        info: { icono: 'bi-info-circle-fill', titulo: 'Aviso' },
    };
    /** Escapa texto escrito por el usuario antes de insertarlo como HTML. */
    function maEscapar(texto) {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    }

    function maToast(tipo, mensaje, titulo = null) {
        const t = _TOAST_TIPOS[tipo] || _TOAST_TIPOS.info;
        const cont = document.getElementById('maToasts');
        if (!cont) return;
        const el = document.createElement('div');
        el.className = `toast-ma toast-${tipo in _TOAST_TIPOS ? tipo : 'info'}`;
        el.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
        el.innerHTML = `<i class="bi ${t.icono} toast-icono"></i>
            <div class="toast-texto"><strong></strong><span></span></div>
            <button type="button" class="toast-cerrar" aria-label="Cerrar"><i class="bi bi-x"></i></button>
            <div class="toast-progreso"></div>`;
        el.querySelector('strong').textContent = titulo || t.titulo;
        el.querySelector('span').textContent = mensaje || '';
        const duracion = tipo === 'error' ? 6000 : 3500;
        el.querySelector('.toast-progreso').style.animationDuration = duracion + 'ms';
        const cerrar = () => { el.classList.add('saliendo'); setTimeout(() => el.remove(), 200); };
        el.querySelector('.toast-cerrar').addEventListener('click', cerrar);
        let timer = setTimeout(cerrar, duracion);
        el.addEventListener('mouseenter', () => { clearTimeout(timer); el.classList.add('pausado'); });
        el.addEventListener('mouseleave', () => { el.classList.remove('pausado'); timer = setTimeout(cerrar, 1500); });
        cont.appendChild(el);
        while (cont.children.length > 4) cont.firstElementChild.remove();
    }

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
                    maToast('error', data.message || 'Ocurrió un error inesperado.');
                }
                return null;
            }
            return data;
        } catch (e) {
            maToast('error', 'Revisa tu conexión a internet e intenta de nuevo.', 'Sin conexión');
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
        document.querySelectorAll(`[data-aviso-id="${id}"]`).forEach(el => el.remove());
        const cont = document.getElementById('contadorAvisos');
        const quedan = document.querySelectorAll('#listaAvisos .aviso-item').length;
        if (cont) { quedan ? (cont.textContent = quedan) : cont.remove(); }
        if (!quedan) document.getElementById('listaAvisos').innerHTML = '<div class="vacio py-4"><i class="bi bi-bell-slash"></i>No hay avisos por ahora.</div>';
    }

    // ---------- Ventana "Ver": perfiles y detalles sin salir de la pantalla ----------
    // Cualquier enlace a /alumnos/{id} o /maestros/{id} se abre en la ventana.
    const _VISTA_RUTAS = /^\/(alumnos|maestros|especialidades|egresos|caja-chica|recitales|avisos|planilla|periodos|horarios|usuarios)\/\d+\/?$/;
    let _vistaModal = null, _vistaHistorial = [];

    function _esRutaVista(href) {
        try {
            const u = new URL(href, location.origin);
            return u.origin === location.origin && _VISTA_RUTAS.test(u.pathname);
        } catch (e) { return false; }
    }

    async function maAbrirVista(url, { agregarHistorial = true } = {}) {
        _vistaModal = _vistaModal || new bootstrap.Modal('#modalVista');
        const cuerpo = document.getElementById('vistaCuerpo');
        const titulo = document.getElementById('vistaTitulo');
        if (!document.getElementById('modalVista').classList.contains('show')) { _vistaHistorial = []; _vistaModal.show(); }
        if (agregarHistorial) _vistaHistorial.push(url);
        document.getElementById('vistaAtras').hidden = _vistaHistorial.length < 2;
        cuerpo.innerHTML = '<div class="cargando"><span class="spinner-border spinner-border-sm"></span> Cargando…</div>';
        try {
            const res = await fetch(url, { headers: { 'X-Modal': '1', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error(res.status);
            const html = await res.text();
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            const frag = tmp.querySelector('.vista-fragmento');
            titulo.textContent = frag?.dataset.titulo || '';
            cuerpo.innerHTML = frag ? frag.innerHTML : html;
            cuerpo.scrollTop = 0;
        } catch (e) {
            cuerpo.innerHTML = '<div class="vacio"><i class="bi bi-wifi-off"></i>No se pudo cargar. Intenta de nuevo.</div>';
        }
    }
    function maCerrarVista() { _vistaModal?.hide(); }

    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || a.hasAttribute('data-sin-ventana')) return;
        if (_esRutaVista(a.href)) { e.preventDefault(); maAbrirVista(a.href); }
    });
    // Formularios GET dentro de la ventana (ej. cambiar el periodo en el perfil del maestro).
    document.getElementById('vistaCuerpo').addEventListener('submit', (e) => {
        const f = e.target;
        if ((f.method || 'get').toLowerCase() !== 'get') return;
        e.preventDefault();
        const url = new URL(f.action || _vistaHistorial[_vistaHistorial.length - 1], location.origin);
        url.search = new URLSearchParams(new FormData(f)).toString();
        _vistaHistorial[_vistaHistorial.length - 1] = url.toString();
        maAbrirVista(url.toString(), { agregarHistorial: false });
    });
    document.getElementById('vistaCuerpo').addEventListener('change', (e) => {
        // Filtros dentro de la ventana: se recarga solo la ventana.
        if (e.target.form?.matches('[data-autofiltro]')) { e.stopImmediatePropagation(); e.target.form.requestSubmit(); }
    }, true);

    // ---------- Filtros automaticos ----------
    // Todo <form method="GET" data-autofiltro> se aplica solo: al cambiar un
    // select, fecha o numero, y 600 ms despues de dejar de escribir en un
    // texto. No hace falta boton "Filtrar". Al recargar, el cursor vuelve al
    // campo donde se estaba escribiendo.
    (function () {
        const CLAVE = 'ma_autofiltro_foco';
        document.querySelectorAll('.main-content form[data-autofiltro]').forEach(form => {
            let timer = null;
            const enviar = (campo) => {
                try { if (campo?.name) sessionStorage.setItem(CLAVE, JSON.stringify({ name: campo.name, pos: campo.selectionStart ?? null })); } catch (e) {}
                form.requestSubmit ? form.requestSubmit() : form.submit();
            };
            form.addEventListener('change', (e) => {
                if (e.target.matches('input[type=text], input[type=search]')) return;
                if (e.target.matches('input[type=number]') && !e.target.value) return;
                enviar();
            });
            form.addEventListener('input', (e) => {
                if (!e.target.matches('input[type=text], input[type=search]')) return;
                clearTimeout(timer);
                timer = setTimeout(() => enviar(e.target), 600);
            });
        });
        try {
            const foco = JSON.parse(sessionStorage.getItem(CLAVE) || 'null');
            sessionStorage.removeItem(CLAVE);
            const campo = foco && document.querySelector(`.main-content form[data-autofiltro] [name="${foco.name}"]`);
            if (campo) { campo.focus(); if (foco.pos !== null) campo.setSelectionRange(foco.pos, foco.pos); }
        } catch (e) {}
    })();
    document.getElementById('vistaAtras').addEventListener('click', () => {
        _vistaHistorial.pop();
        maAbrirVista(_vistaHistorial[_vistaHistorial.length - 1], { agregarHistorial: false });
        document.getElementById('vistaAtras').hidden = _vistaHistorial.length < 2;
    });

    // ---------- Buscador rapido (Ctrl + K) ----------
    (function () {
        const caja = document.getElementById('buscador');
        const input = document.getElementById('buscadorInput');
        const lista = document.getElementById('buscadorResultados');
        if (!input) return;
        let timer = null, activo = -1, controlador = null;

        const items = () => [...lista.querySelectorAll('.resultado')];
        const marcar = (i) => { items().forEach((el, j) => el.classList.toggle('activo', j === i)); activo = i; };
        const cerrar = () => { lista.hidden = true; activo = -1; };
        const esc = (t) => (t || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));

        async function buscar() {
            const q = input.value.trim();
            if (q.length < 2) { cerrar(); return; }
            controlador?.abort();
            controlador = new AbortController();
            try {
                const r = await fetch(`{{ route('buscar') }}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json' }, signal: controlador.signal });
                const d = await r.json();
                const grupo = (titulo, arr, icono) => arr.length ? `<div class="resultado-grupo">${titulo}</div>` + arr.map(x =>
                    `<a class="resultado" href="${x.url}"><span class="resultado-icono"><i class="bi ${icono}"></i></span><span class="min-w-0"><span class="d-block text-truncate">${esc(x.nombre)}</span><span class="d-block small text-muted text-truncate">${esc(x.detalle)}</span></span></a>`).join('') : '';
                const html = grupo('Alumnos', d.alumnos, 'bi-person') + grupo('Maestros', d.maestros, 'bi-person-badge');
                lista.innerHTML = html || `<div class="vacio py-3">Sin resultados para “${esc(q)}”.</div>`;
                lista.hidden = false;
                marcar(items().length ? 0 : -1);
            } catch (e) {}
        }
        input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(buscar, 200); });
        input.addEventListener('focus', () => { if (lista.innerHTML && input.value.trim().length >= 2) lista.hidden = false; });
        input.addEventListener('keydown', (e) => {
            const n = items().length;
            if (e.key === 'ArrowDown' && n) { e.preventDefault(); marcar((activo + 1) % n); }
            else if (e.key === 'ArrowUp' && n) { e.preventDefault(); marcar((activo - 1 + n) % n); }
            else if (e.key === 'Enter' && activo >= 0) { e.preventDefault(); items()[activo].click(); }
            else if (e.key === 'Escape') { cerrar(); input.blur(); caja.classList.remove('abierto'); }
        });
        lista.addEventListener('click', (e) => { if (e.target.closest('.resultado')) { cerrar(); caja.classList.remove('abierto'); } });
        document.addEventListener('click', (e) => { if (!caja.contains(e.target) && !e.target.closest('#btnBuscarMovil')) { cerrar(); caja.classList.remove('abierto'); } });
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); caja.classList.add('abierto'); input.focus(); input.select(); }
        });
        document.getElementById('btnBuscarMovil')?.addEventListener('click', () => { caja.classList.add('abierto'); input.focus(); });
    })();

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
        document.querySelectorAll('table.table:not(.historial)').forEach(tabla => {
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
@endif
