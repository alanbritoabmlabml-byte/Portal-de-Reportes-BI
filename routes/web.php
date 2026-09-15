<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\TrazabilidadController;
use Illuminate\Support\Facades\Route;

// ---------- Acceso ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
    Route::post('/login', [LoginController::class, 'entrar'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'salir'])->middleware('auth')->name('logout');

// ---------- Con sesión ----------
Route::middleware('auth')->group(function () {
    Route::get('/', MenuController::class)->name('menu');

    Route::get('/bi/{area}', [AreaController::class, 'show'])->name('bi.area');
    Route::get('/bi/reporte/{reporte}/demo', [AreaController::class, 'demo'])->name('demo.reporte');

    Route::post('/favoritos', [FavoritoController::class, 'alternar'])->name('favoritos.alternar');

    Route::post('/tema', [TemaController::class, 'guardar'])->name('tema.guardar');

    // Señales del navegador para medir permanencia y clics externos
    Route::post('/trazabilidad/latido', [TrazabilidadController::class, 'latido'])->name('trazabilidad.latido');
    Route::post('/trazabilidad/evento', [TrazabilidadController::class, 'evento'])->name('trazabilidad.evento');

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'leerTodas'])->name('notificaciones.leerTodas');
    Route::post('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');

    // ---------- Administración ----------
    Route::prefix('admin')->name('admin.')->middleware('can:administrar')->group(function () {
        Route::resource('reportes', Admin\ReporteController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::put('reportes/{reporte}/accesos', [Admin\ReporteController::class, 'accesos'])->name('reportes.accesos');

        Route::resource('usuarios', Admin\UsuarioController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['usuarios' => 'usuario']);

        Route::resource('tarjetas', Admin\AccesoController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tarjetas' => 'acceso']);

        Route::get('estructura', [Admin\EstructuraController::class, 'index'])->name('estructura.index');
        Route::post('departamentos', [Admin\EstructuraController::class, 'guardarDepartamento'])->name('departamentos.store');
        Route::put('departamentos/{departamento}', [Admin\EstructuraController::class, 'actualizarDepartamento'])->name('departamentos.update');
        Route::delete('departamentos/{departamento}', [Admin\EstructuraController::class, 'eliminarDepartamento'])->name('departamentos.destroy');
        Route::post('areas', [Admin\EstructuraController::class, 'guardarArea'])->name('areas.store');
        Route::put('areas/{area:id}', [Admin\EstructuraController::class, 'actualizarArea'])->name('areas.update');
        Route::delete('areas/{area:id}', [Admin\EstructuraController::class, 'eliminarArea'])->name('areas.destroy');

        Route::post('notificaciones', [Admin\NotificacionController::class, 'store'])->name('notificaciones.store');

        // Orden con flechas (tarjetas, departamentos, áreas y reportes)
        Route::put('orden/{tipo}/{id}/{direccion}', [Admin\OrdenController::class, 'mover'])
            ->whereIn('tipo', ['tarjeta', 'departamento', 'area', 'reporte'])
            ->whereNumber('id')
            ->whereIn('direccion', ['arriba', 'abajo'])
            ->name('orden.mover');

        // ---------- Trazabilidad ----------
        Route::get('trazabilidad', [Admin\TrazabilidadController::class, 'index'])->name('trazabilidad.index');
        Route::get('trazabilidad/sesion/{sesion}', [Admin\TrazabilidadController::class, 'sesion'])->name('trazabilidad.sesion');
        Route::get('trazabilidad/exportar/{formato}', [Admin\TrazabilidadController::class, 'exportar'])
            ->whereIn('formato', ['xlsx', 'pdf'])
            ->name('trazabilidad.exportar');
    });
});
