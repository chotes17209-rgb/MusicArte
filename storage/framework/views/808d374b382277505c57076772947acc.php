<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('titulo', 'Panel'); ?> - Musica Arte</title>
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">

    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.all.min.js"></script>
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ma-morado: #3d2c8d;
            --ma-morado-oscuro: #2a1e63;
            --ma-dorado: #f2b134;
            --ma-gris: #f4f5f9;
        }
        html, body { overflow-x: hidden; }
        body { font-family: 'Poppins', sans-serif; background: var(--ma-gris); }

        .sidebar {
            width: 250px; height: 100vh; background: linear-gradient(180deg, var(--ma-morado-oscuro), var(--ma-morado));
            position: fixed; top: 0; left: 0; z-index: 1030;
            display: flex; flex-direction: column;
            transition: transform .25s ease, width .25s ease;
        }
        .sidebar .logo-box { flex: 0 0 auto; padding: 1.25rem 1rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,.15); }
        .sidebar .logo-box img { width: 78px; height: 78px; border-radius: 50%; object-fit: cover; box-shadow: 0 0 0 3px rgba(255,255,255,.25); }
        .sidebar .logo-box h6 { color: #fff; margin: .5rem 0 0; font-weight: 600; letter-spacing: .5px; }

        /* Nav con scroll propio: asi siempre se puede bajar a ver todas las opciones */
        .sidebar nav {
            flex: 1 1 auto; overflow-y: auto; overflow-x: hidden; flex-wrap: nowrap;
            scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.25) transparent;
        }
        .sidebar nav::-webkit-scrollbar { width: 5px; }
        .sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); border-radius: 10px; }

        .sidebar .nav-link { color: rgba(255,255,255,.8); padding: .65rem 1.2rem; font-size: .92rem; display: flex; align-items: center; gap: .6rem; border-left: 3px solid transparent; white-space: nowrap; }
        .sidebar .nav-link i { font-size: 1.05rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.08); border-left-color: var(--ma-dorado); }
        .sidebar .nav-section { color: rgba(255,255,255,.45); font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; padding: 1rem 1.2rem .3rem; }

        .content-wrap { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; transition: margin-left .25s ease; }

        /* Estado colapsado (solo iconos, texto oculto de verdad) */
        .sidebar.collapsed { width: 74px; }
        .sidebar.collapsed .logo-box h6,
        .sidebar.collapsed .logo-box small,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-link .nav-text { display: none; }
        .sidebar.collapsed .logo-box img { width: 42px; height: 42px; }
        .sidebar.collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .content-wrap.collapsed { margin-left: 74px; }

        .btn-collapse-sidebar {
            position: absolute; top: 1.1rem; right: -14px; width: 28px; height: 28px;
            border-radius: 50%; background: var(--ma-dorado); color: #3a2900; border: none;
            display: flex; align-items: center; justify-content: center; z-index: 1031;
        }

        .topbar { background: #fff; border-bottom: 1px solid #e9e9f2; padding: .7rem 1.5rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020; }
        .main-content { padding: 1.5rem; flex: 1; }
        .btn-morado { background: var(--ma-morado); border-color: var(--ma-morado); color: #fff; }
        .btn-morado:hover { background: var(--ma-morado-oscuro); border-color: var(--ma-morado-oscuro); color: #fff; }
        .badge-rol-admin { background: var(--ma-dorado); color: #3a2900; }
        .badge-rol-recepcion { background: #6c757d; }
        .card-kpi { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(61,44,141,.08); }
        .card { border-radius: 14px; border: 1px solid #edeef5; }
        .table thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .4px; color: #6b6b80; border-bottom-width: 1px; }
        .btn-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
        .aviso-flotante { border-left: 5px solid var(--ma-morado); }
        .aviso-urgente { border-left-color: #dc3545 !important; }
        .aviso-advertencia { border-left-color: #f2b134 !important; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); width: 250px !important; }
            .sidebar.show { transform: translateX(0); }
            .sidebar.collapsed .nav-link .nav-text,
            .sidebar.collapsed .logo-box h6,
            .sidebar.collapsed .logo-box small,
            .sidebar.collapsed .nav-section { display: inline; }
            .sidebar.collapsed .nav-link { justify-content: flex-start; padding-left: 1.2rem; }
            .content-wrap, .content-wrap.collapsed { margin-left: 0; }
            .btn-collapse-sidebar { display: none !important; }
        }
        .toggler-mobile { display: none; }
        @media (max-width: 991px) { .toggler-mobile { display: inline-flex; } }
    </style>
    <?php echo $__env->yieldPushContent('estilos'); ?>
</head>
<body>

<div class="sidebar" id="sidebar">
    <button class="btn-collapse-sidebar d-none d-lg-flex" id="btnCollapseSidebar" title="Contraer menu">
        <i class="bi bi-chevron-left" id="iconCollapseSidebar"></i>
    </button>
    <div class="logo-box">
        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Musica Arte">
        <h6>MUSICARTE</h6>
        <small class="text-white-50">Centro Cultural</small>
    </div>
    <nav class="nav flex-column py-2">
        <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>"><i class="bi bi-speedometer2"></i> <span class="nav-text">Dashboard</span></a>

        <div class="nav-section">Academico</div>
        <a class="nav-link <?php echo e(request()->routeIs('alumnos.*') ? 'active' : ''); ?>" href="<?php echo e(route('alumnos.index')); ?>"><i class="bi bi-people"></i> <span class="nav-text">Alumnos</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('maestros.*') ? 'active' : ''); ?>" href="<?php echo e(route('maestros.index')); ?>"><i class="bi bi-person-badge"></i> <span class="nav-text">Maestros</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('especialidades.*') ? 'active' : ''); ?>" href="<?php echo e(route('especialidades.index')); ?>"><i class="bi bi-music-note-list"></i> <span class="nav-text">Especialidades</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('periodos.*') ? 'active' : ''); ?>" href="<?php echo e(route('periodos.index')); ?>"><i class="bi bi-calendar-range"></i> <span class="nav-text">Periodos</span></a>

        <div class="nav-section">Clases</div>
        <a class="nav-link <?php echo e(request()->routeIs('calendario.*') ? 'active' : ''); ?>" href="<?php echo e(route('calendario.index')); ?>"><i class="bi bi-calendar3"></i> <span class="nav-text">Calendario</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('horarios.*') ? 'active' : ''); ?>" href="<?php echo e(route('horarios.index')); ?>"><i class="bi bi-clock-history"></i> <span class="nav-text">Horarios</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('asistencia.*') ? 'active' : ''); ?>" href="<?php echo e(route('asistencia.index')); ?>"><i class="bi bi-clipboard-check"></i> <span class="nav-text">Asistencia</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('recitales.*') ? 'active' : ''); ?>" href="<?php echo e(route('recitales.index')); ?>"><i class="bi bi-mic"></i> <span class="nav-text">Recitales/Eventos</span></a>

        <div class="nav-section">Administracion</div>
        <a class="nav-link <?php echo e(request()->routeIs('pagos.*') ? 'active' : ''); ?>" href="<?php echo e(route('pagos.index')); ?>"><i class="bi bi-cash-coin"></i> <span class="nav-text">Pagos</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('egresos.*') ? 'active' : ''); ?>" href="<?php echo e(route('egresos.index')); ?>"><i class="bi bi-receipt"></i> <span class="nav-text">Egresos</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('caja-chica.*') ? 'active' : ''); ?>" href="<?php echo e(route('caja-chica.index')); ?>"><i class="bi bi-wallet2"></i> <span class="nav-text">Caja Chica</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('planilla.*') ? 'active' : ''); ?>" href="<?php echo e(route('planilla.index')); ?>"><i class="bi bi-file-earmark-person"></i> <span class="nav-text">Planilla Maestros</span></a>

        <div class="nav-section">General</div>
        <a class="nav-link <?php echo e(request()->routeIs('avisos.*') ? 'active' : ''); ?>" href="<?php echo e(route('avisos.index')); ?>"><i class="bi bi-megaphone"></i> <span class="nav-text">Avisos</span></a>
        <a class="nav-link <?php echo e(request()->routeIs('reportes.*') ? 'active' : ''); ?>" href="<?php echo e(route('reportes.index')); ?>"><i class="bi bi-graph-up"></i> <span class="nav-text">Reportes</span></a>
    </nav>
</div>

<div class="content-wrap" id="contentWrap">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light toggler-mobile" id="btnToggleSidebar"><i class="bi bi-list fs-4"></i></button>
            <h5 class="mb-0 fw-semibold text-dark"><?php echo $__env->yieldContent('titulo', 'Panel'); ?></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light position-relative btn-icon" id="btnAvisos" title="Avisos">
                <i class="bi bi-bell fs-5"></i>
                <?php if(($avisosFlotantes ?? collect())->count() > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo e($avisosFlotantes->count()); ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-sm-inline"><?php echo e(auth()->user()->name); ?></span>
                    <span class="badge <?php echo e(auth()->user()->esAdmin() ? 'badge-rol-admin' : 'badge-rol-recepcion'); ?>"><?php echo e(auth()->user()->rolLabel()); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <form action="<?php echo e(route('logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        <?php echo $__env->yieldContent('contenido'); ?>
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
                <?php $__empty_1 = true; $__currentLoopData = ($avisosFlotantes ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aviso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="alert aviso-flotante aviso-<?php echo e($aviso->tipo); ?> mb-2" data-aviso-id="<?php echo e($aviso->id); ?>">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo e($aviso->titulo); ?></strong>
                            <button class="btn-close" style="font-size:.7rem" onclick="descartarAviso(<?php echo e($aviso->id); ?>, this)"></button>
                        </div>
                        <div class="small text-muted mt-1"><?php echo nl2br(e($aviso->mensaje)); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0">No hay avisos activos por el momento.</p>
                <?php endif; ?>
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

    async function maFetch(url, options = {}) {
        options.headers = Object.assign({
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }, options.headers || {});

        try {
            const res = await fetch(url, options);
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

    // Muestra automaticamente el popup de avisos urgentes al cargar el dashboard
    <?php if(($avisosFlotantes ?? collect())->where('tipo', 'urgente')->count() > 0 && request()->routeIs('dashboard')): ?>
        window.addEventListener('DOMContentLoaded', () => {
            new bootstrap.Modal('#modalAvisos').show();
        });
    <?php endif; ?>

    // Mensajes flash de sesion (redirects normales)
    <?php if(session('success')): ?>
        window.addEventListener('DOMContentLoaded', () => maToast('success', <?php echo json_encode(session('success'), 15, 512) ?>));
    <?php endif; ?>
    <?php if(session('error')): ?>
        window.addEventListener('DOMContentLoaded', () => maToast('error', <?php echo json_encode(session('error'), 15, 512) ?>));
    <?php endif; ?>
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\XAMPP\htdocs\musicarte\resources\views/layouts/app.blade.php ENDPATH**/ ?>