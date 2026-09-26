{{-- Pestañas de la seccion Horarios: las tres formas de ver lo mismo. --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('horarios.tablero') ? 'active' : '' }}" href="{{ route('horarios.tablero') }}"><i class="bi bi-grid-3x3-gap me-1"></i> Cuadro por maestro</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('horarios.mensual') ? 'active' : '' }}" href="{{ route('horarios.mensual') }}"><i class="bi bi-calendar-week me-1"></i> Por alumno (mes)</a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('horarios.index') ? 'active' : '' }}" href="{{ route('horarios.index') }}"><i class="bi bi-list-ul me-1"></i> Lista y edición</a></li>
</ul>
