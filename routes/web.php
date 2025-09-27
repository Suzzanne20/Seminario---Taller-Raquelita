<?php

use Illuminate\Support\Facades\Route;
//Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TipoInsumoController;


/* ────────────────────────────────────────────────────────────────
 | PÚBLICO (sin autenticación)
 *────────────────────────────────────────────────────────────────*/

use App\Http\Controllers\RecepcionController;

use App\Http\Controllers\InventarioController;


//Landing pública

Route::view('/', 'home')->name('home');
Route::view('/acceso', 'auth.access')->name('acceso'); //login y registro
//Ruta de autenticación Breeze
require __DIR__.'/auth.php';

/* ────────────────────────────────────────────────────────────────
 | ZONA AUTENTICADA (login + email verificado)
 *────────────────────────────────────────────────────────────────*/
Route::middleware(['auth', 'verified'])->group(function () {

    // Home con autenticacion
    Route::view('/welcome', 'welcome')->name('welcome');

    // Perfil del usuario autenticado
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ───────────────────────────────────────────────────────
     | ADMIN  (acceso total)
     *────────────────────────────────────────────────────────*/
    Route::middleware('role:admin')->group(function () {
        //Dashboard de datos y metricas
        Route::view('/dashboard', 'dashboard')->name('dashboard');

        //Vehiculos
        Route::resource('vehiculos', VehiculoController::class);

        // Gestión de usuarios
        Route::resource('users', UsersController::class)
            ->only(['index','store','update','destroy']);

        // Bodega / Inventario
        Route::delete('insumos/eliminar-multiples', [InsumoController::class, 'destroyMultiple'])
            ->name('insumos.destroyMultiple');

        //Insumos / Tipo de insumos
        Route::resource('insumos', InsumoController::class); //CRUD
        Route::resource('tipo-insumos', TipoInsumoController::class);
        Route::get('insumos', [TipoInsumoController::class, 'index'])->name('insumos.index');

        //Ordenes de Trabajo <----------------
        Route::get('/ordenes',                 [OrdenTrabajoController::class, 'index'])->name('ordenes.index');
        Route::get('/ordenes/crear',           [OrdenTrabajoController::class, 'create'])->name('ordenes.create');
        Route::post('/ordenes',                [OrdenTrabajoController::class, 'store'])->name('ordenes.store');
        Route::get('/ordenes/{orden}/editar',  [OrdenTrabajoController::class, 'edit'])->name('ordenes.edit');
        Route::put('/ordenes/{orden}',         [OrdenTrabajoController::class, 'update'])->name('ordenes.update');
        Route::delete('/ordenes/{orden}',      [OrdenTrabajoController::class, 'destroy'])->name('ordenes.destroy');



    });

    /* ───────────────────────────────────────────────────────
     | SECRETARIA  (y ADMIN)
     | - Clientes, Órdenes de trabajo, Cotizaciones, Vehículos
     *────────────────────────────────────────────────────────*/
    Route::middleware('role:admin|secretaria')->group(function () {

        // Clientes (CRUD)
        Route::resource('clientes', ClienteController::class);

        // Órdenes de trabajo
        Route::resource('ordenes', OrdenTrabajoController::class);
        // si tus vistas usan la ruta corta /ordenes (listado):
        Route::get('/ordenes', [OrdenTrabajoController::class, 'index'])->name('ordenes.index');

        // Cotizaciones y aprobacion
        Route::resource('cotizaciones', CotizacionController::class);
        Route::post('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class, 'aprobar'])
            ->name('cotizaciones.aprobar');

        // Vehículos
        Route::resource('vehiculos', VehiculoController::class)->only(['index','create','store','edit','update','show']);
    });

    /* ───────────────────────────────────────────────────────
     | MECÁNICO  (y ADMIN)
     | - Inspecciones 360 (cuando exista controlador/vistas)
     *────────────────────────────────────────────────────────*/
    // Route::middleware('role:admin|mecanico')->group(function () {
    //     Route::get('/inspecciones360', [InspeccionController::class, 'index'])
    //         ->name('inspecciones.index');
    // });
});



// Inpecccion xd
Route::get('/inspecciones/inicio', [RecepcionController::class, 'start'])->name('inspecciones.start');

// Registrar
Route::get('/inspecciones/crear', [RecepcionController::class,'create'])->name('inspecciones.create');
Route::post('/inspecciones',      [RecepcionController::class,'store'])->name('inspecciones.store');

// **Listado (GET) -> necesario para el botón Modificar**
Route::get('/inspecciones',       [RecepcionController::class,'index'])->name('inspecciones.index');

Route::get('/inspecciones/{rec}',        [RecepcionController::class,'show'])->name('inspecciones.show');
Route::get('/inspecciones/{rec}/editar', [RecepcionController::class,'edit'])->name('inspecciones.edit');
Route::put('/inspecciones/{rec}',        [RecepcionController::class,'update'])->name('inspecciones.update');
Route::delete('/inspecciones/{rec}',     [RecepcionController::class,'destroy'])->name('inspecciones.destroy');
//Rutas sensibles para acceso de usuarios autenticados
Route::resource('clientes',              ClienteController::class)->middleware('auth');
Route::get('/ordenes',                  [OrdenTrabajoController::class, 'index'])->name('ordenes.index')->middleware('auth');

// Perfil de usuario
Route::get('/profile',                  [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile',                [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile',               [ProfileController::class, 'destroy'])->name('profile.destroy');

// Clientes
Route::resource('clientes', ClienteController::class);


// Cotizaciones
Route::resource('cotizaciones',                    CotizacionController::class);
Route::post('cotizaciones/{cotizacione}/aprobar', [CotizacionController::class,'aprobar']) ->name('cotizaciones.aprobar');

//Ordenes de Trabajo <----------------
Route::get('/ordenes',                 [OrdenTrabajoController::class, 'index'])->name('ordenes.index');
Route::get('/ordenes/crear',           [OrdenTrabajoController::class, 'create'])->name('ordenes.create');
Route::post('/ordenes',                [OrdenTrabajoController::class, 'store'])->name('ordenes.store');
Route::get('/ordenes/{orden}/editar',  [OrdenTrabajoController::class, 'edit'])->name('ordenes.edit');
Route::put('/ordenes/{orden}',         [OrdenTrabajoController::class, 'update'])->name('ordenes.update');
Route::delete('/ordenes/{orden}',      [OrdenTrabajoController::class, 'destroy'])->name('ordenes.destroy');

Route::resource('cotizaciones', CotizacionController::class);
Route::post('cotizaciones/{cotizacione}/aprobar', [CotizacionController::class,'aprobar'])
    ->name('cotizaciones.aprobar');



//Rutas para Insumos
Route::delete('insumos/eliminar-multiples', [InsumoController::class, 'destroyMultiple'])->name('insumos.destroyMultiple');
Route::resource('insumos', InsumoController::class);

//Rutas para TiposInsumo
Route::resource('tipo-insumos', TipoInsumoController::class);

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//inventario

Route::get('/inventario', [InventarioController::class, 'index'])
    ->name('inventario.index')
    ->middleware('auth');

