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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\OrdenCompraDetalleController;


/* ────────────────────────────────────────────────────────────────
 | PÚBLICO (sin autenticación)
 *────────────────────────────────────────────────────────────────*/

use App\Http\Controllers\RecepcionController;

use App\Http\Controllers\InventarioController;


//Landing pública

Route::get('/', [LandingController::class, 'home'])->name('home');
// Para el tracking de la orden de Trabajo
Route::get('/track', [LandingController::class, 'home'])->name('track');


Route::view('/acceso', 'auth.access')->name('acceso'); //login y registro
//Ruta de autenticación Breeze
require __DIR__.'/auth.php';

/* ────────────────────────────────────────────────────────────────
 | ZONA AUTENTICADA (login + email verificado)
 *────────────────────────────────────────────────────────────────*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'welcome')->name('welcome');
    Route::view('/welcome', 'welcome');


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
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth','verified'])
            ->name('dashboard');

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
        Route::resource('ordenes',              OrdenTrabajoController::class) ->except(['edit']) ->parameters(['ordenes' => 'orden']);
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


        Route::get('/ordenes', [OrdenTrabajoController::class, 'index'])->name('ordenes.index');

        // Cotizaciones y aprobacion
        Route::resource('cotizaciones', CotizacionController::class);
        Route::post('cotizaciones/{cotizacion}/aprobar', [CotizacionController::class, 'aprobar'])
            ->name('cotizaciones.aprobar');
        Route::post('cotizaciones/{cotizacione}/rechazar', [CotizacionController::class, 'rechazar'])->name('cotizaciones.rechazar');


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

Route::get('/fotos/{foto}', [RecepcionController::class, 'streamFoto'])
     ->name('fotos.stream');

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




























































































































// Rutas para Órdenes de Compra
Route::get('/ordenes_compras', [OrdenCompraController::class, 'index'])->name('ordenes_compras.index');
Route::get('/ordenes_compras/crear', [OrdenCompraController::class, 'create'])->name('ordenes_compras.create');
Route::post('/ordenes_compras', [OrdenCompraController::class, 'store'])->name('ordenes_compras.store');

// Rutas PUT y DELETE antes de las que usan {orden}
Route::put('/ordenes_compras/{id}', [OrdenCompraController::class, 'update'])->name('ordenes_compras.update');
Route::delete('/ordenes_compras/{id}', [OrdenCompraController::class, 'destroy'])->name('ordenes_compras.destroy');

// Rutas GET con parámetros
Route::get('/ordenes_compras/{id}/editar', [OrdenCompraController::class, 'edit'])->name('ordenes_compras.edit');
Route::get('/ordenes_compras/{id}', [OrdenCompraController::class, 'show'])->name('ordenes_compras.show');

// Ruta para actualizar estado de la orden desde el index
Route::patch('/ordenes_compras/{id}/estado', [OrdenCompraController::class, 'updateEstado'])
    ->name('ordenes_compras.updateEstado');

// Ruta para finalizar la Orden de Compra
Route::post('/ordenes_compras/{id}/finalizar', [OrdenCompraController::class, 'finalizar'])
    ->name('ordenes_compras.finalizar');

// Rutas de Detalles de ORdenes de Compras
Route::resource('ordencompra_detalle', OrdenCompraDetalleController::class);

