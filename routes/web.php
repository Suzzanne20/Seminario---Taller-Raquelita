<?php
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;           // Admin > gestión de usuarios
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\TipoInsumoController;
// use App\Http\Controllers\InspeccionController; cuando se habilite

// ────────────────────────────────────────────────────────────────
//   PÚBLICO (sin autenticación)

    Route::view('/', 'home')->name('home'); // Landing pública
    Route::view('/acceso', 'auth.access')->name('acceso'); // Pantalla de acceso (login/recuperación)
    require __DIR__.'/auth.php'; // Rutas de autenticación Breeze

// ────────────────────────────────────────────────────────────────
//   ZONA DE AUTENTICACIÓN (requiere login/verificación)

    Route::middleware(['auth', 'verified'])->group(function () {

        // ── Dashboard interna para usuarios autenticados
        Route::view('/dashboard', 'welcome')->name('dashboard');
        Route::view('/welcome', 'welcome')->name('welcome');

        // ── Perfil (usuarios autenticados)
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
// ────────────────────────────────────────────────────────────
//   SECRETARIA (y ADMIN) – Clientes, Órdenes, Cotizaciones

Route::middleware(['auth','role:admin|secretaria'])->group(function () {

    // Clientes
    Route::resource('clientes', ClienteController::class);

    // Órdenes de trabajo
    Route::resource('ordenes', OrdenTrabajoController::class);
    // Si tu navbar también usa /ordenes => name('ordenes.index'):
    Route::get('/ordenes', [OrdenTrabajoController::class, 'index'])->name('ordenes.index');

    // Cotizaciones
    Route::resource('cotizaciones', CotizacionController::class);
    Route::post('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class,'aprobar'])
        ->name('cotizaciones.aprobar');

    // Vehículos
    Route::resource('vehiculos', VehiculoController::class);

});
// ────────────────────────────────────────────────────────────
//   MECÁNICO > Cuando se habilite inspecciones

// Route::middleware(['auth','role:mecanico'])->group(function () {
//     Route::get('/inspecciones360', [InspeccionController::class, 'index'])->name('inspecciones.index');
// });

// ────────────────────────────────────────────────────────────
//   ADMINISTRADOR (acceso completo) - Usuarios, catalogos y bodega

    Route::middleware(['auth','role:admin'])->group(function () {

        // Usuarios (ABM)
        Route::resource('users', UsersController::class)->only(['index','store','update','destroy']);

        // Vehículos
        Route::resource('vehiculos', VehiculoController::class);

        // Bodega / Insumos
        Route::delete('insumos/eliminar-multiples', [InsumoController::class, 'destroyMultiple'])
            ->name('insumos.destroyMultiple');
        Route::resource('insumos', InsumoController::class);

        // Tipos de insumo
        Route::resource('tipo-insumos', TipoInsumoController::class);
        //Route::get('insumos', [TipoInsumoController::class, 'index'])->name('insumos.index');
    });

