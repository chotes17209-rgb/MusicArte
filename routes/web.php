<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisoController;
use App\Http\Controllers\CajaChicaController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\PlanillaController;
use App\Http\Controllers\ReciboController;
use App\Http\Controllers\RecitalController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticacion
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Aplicacion (requiere sesion iniciada)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---------------------------------------------------------------
    // Avisos flotantes: ambos roles pueden verlos, admin y recepcion
    // pueden publicarlos (se usa para comunicados internos).
    // ---------------------------------------------------------------
    Route::post('/avisos/{aviso}/descartar', [AvisoController::class, 'descartar'])->name('avisos.descartar');
    Route::resource('avisos', AvisoController::class)->except(['show', 'create']);

    // ---------------------------------------------------------------
    // Catalogos y operacion diaria (ambos roles, con reglas internas
    // en el controlador para el campo "precio_mensual").
    // ---------------------------------------------------------------
    Route::resource('periodos', PeriodoController::class)->except(['show', 'create']);
    Route::resource('especialidades', EspecialidadController::class)->except(['show', 'create']);
    Route::resource('maestros', MaestroController::class)->except(['show', 'create']);
    Route::resource('alumnos', AlumnoController::class)->except(['show', 'create']);
    Route::get('/horarios/mensual', [HorarioController::class, 'vistaMensual'])->name('horarios.mensual');
    Route::resource('horarios', HorarioController::class)->except(['show', 'create']);
    Route::post('/horarios-generar-clases', [HorarioController::class, 'generarClases'])->name('horarios.generar');

    // ---------------------------------------------------------------
    // Calendario de clases
    // ---------------------------------------------------------------
    Route::get('/calendario', [ClaseController::class, 'index'])->name('calendario.index');
    Route::get('/calendario/eventos', [ClaseController::class, 'eventos'])->name('calendario.eventos');
    Route::post('/clases', [ClaseController::class, 'store'])->name('clases.store');
    Route::get('/clases/{clase}', [ClaseController::class, 'show'])->name('clases.show');
    Route::put('/clases/{clase}', [ClaseController::class, 'update'])->name('clases.update');
    Route::put('/clases/{clase}/mover', [ClaseController::class, 'mover'])->name('clases.mover');
    Route::put('/clases/{clase}/estado', [ClaseController::class, 'cambiarEstado'])->name('clases.estado');
    Route::delete('/clases/{clase}', [ClaseController::class, 'destroy'])->name('clases.destroy');

    // ---------------------------------------------------------------
    // Asistencia
    // ---------------------------------------------------------------
    Route::get('/asistencia', [AsistenciaController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia/{clase}', [AsistenciaController::class, 'marcar'])->name('asistencia.marcar');

    // ---------------------------------------------------------------
    // Recitales / eventos
    // ---------------------------------------------------------------
    Route::resource('recitales', RecitalController::class)->except(['show', 'create']);

    // ---------------------------------------------------------------
    // Modulos SENSIBLES (precios): solo administrador puede escribir.
    // Ambos roles pueden ver el listado (index).
    // ---------------------------------------------------------------
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/anual', [PagoController::class, 'anual'])->name('pagos.anual');
    Route::get('/pagos/{pago}/recibo', [ReciboController::class, 'pdf'])->name('pagos.recibo');
    Route::get('/pagos/{pago}/abonos/{abono}/recibo', [ReciboController::class, 'pdfAbono'])->name('pagos.abono-recibo');
    Route::get('/planilla', [PlanillaController::class, 'index'])->name('planilla.index');

    Route::middleware('role:admin')->group(function () {
        Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
        Route::get('/pagos/{pago}/edit', [PagoController::class, 'edit'])->name('pagos.edit');
        Route::put('/pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
        Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
        Route::post('/pagos/{pago}/abonos', [PagoController::class, 'storeAbono'])->name('pagos.abonos.store');
        Route::delete('/pagos/{pago}/abonos/{abono}', [PagoController::class, 'destroyAbono'])->name('pagos.abonos.destroy');

        Route::post('/planilla/generar', [PlanillaController::class, 'generarDesdeAsistencia'])->name('planilla.generar');
        Route::post('/planilla', [PlanillaController::class, 'store'])->name('planilla.store');
        Route::get('/planilla/{planilla}/edit', [PlanillaController::class, 'edit'])->name('planilla.edit');
        Route::put('/planilla/{planilla}', [PlanillaController::class, 'update'])->name('planilla.update');
        Route::delete('/planilla/{planilla}', [PlanillaController::class, 'destroy'])->name('planilla.destroy');
    });

    // ---------------------------------------------------------------
    // Egresos y Caja Chica (operativo, ambos roles)
    // ---------------------------------------------------------------
    Route::resource('egresos', EgresoController::class)->except(['show', 'create']);
    Route::resource('caja-chica', CajaChicaController::class)->except(['show', 'create']);

    // ---------------------------------------------------------------
    // Reportes
    // ---------------------------------------------------------------
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/alumnos-por-especialidad', [ReporteController::class, 'alumnosPorEspecialidad'])->name('alumnos-especialidad');
        Route::get('/asistencia-mensual', [ReporteController::class, 'asistenciaMensual'])->name('asistencia-mensual');
        Route::get('/ingresos-egresos', [ReporteController::class, 'ingresosEgresos'])->name('ingresos-egresos');
        Route::get('/pagos-pendientes', [ReporteController::class, 'pagosPendientes'])->name('pagos-pendientes');
        Route::get('/planilla-maestros', [ReporteController::class, 'planillaMaestros'])->name('planilla-maestros');
        Route::get('/clases', [ReporteController::class, 'clases'])->name('clases');
    });
});
