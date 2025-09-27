<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TipoInsumoController;
use App\Http\Controllers\InventarioController;

//Landing pública
Route::view('/', 'home')->name('home');

//Ruta de acceso para login y recuperación
Route::view('/acceso', 'auth.access')->name('acceso');
//Ruta de autenticación Breeze
//Ruta de autenticación Breeze
//Ruta de autenticación Breeze
require __DIR__.'/auth.php';

Route::resource('vehiculos', VehiculoController::class);
Route::resource('tipo_insumos', TipoInsumoController::class);
Route::get('insumos', [TipoInsumoController::class, 'index'])->name('insumos.index');


//Rutas de autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'welcome')->name('welcome');
    Route::view('/welcome', 'welcome');
});

Route::middleware(['auth','role:admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\UsersController::class)
        ->only(['index','store','update','destroy']);
});


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

