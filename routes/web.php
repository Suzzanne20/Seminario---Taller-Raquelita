<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TipoInsumoController;

 //Landing pública
Route::view('/', 'home')->name('home');

//Ruta de acceso para login y recuperación
Route::view('/acceso', 'auth.access')->name('acceso');

//Ruta de autenticación Breeze
require __DIR__.'/auth.php';


//Rutas de autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'welcome')->name('dashboard');
    Route::view('/welcome', 'welcome');
});

Route::middleware(['auth','role:admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\UsersController::class)
        ->only(['index','store','update','destroy']);
});


//Rutas sensibles para acceso de usuarios autenticados
Route::resource('clientes', ClienteController::class)->middleware('auth');
Route::get('/ordenes', [OrdenTrabajoController::class, 'index'])->name('ordenes.index')->middleware('auth');

// Perfil de usuario
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Clientes
Route::resource('clientes', ClienteController::class);

// Órdenes de trabajo
Route::get('/ordenes',  [OrdenTrabajoController::class, 'index'])->name('ordenes.index');

// Cotizaciones
Route::resource('cotizaciones', CotizacionController::class);
Route::post('cotizaciones/{cotizacione}/aprobar', [CotizacionController::class,'aprobar'])
    ->name('cotizaciones.aprobar');
















































//Rutas para Insumos
Route::resource('insumos', InsumoController::class);
Route::delete('insumos/eliminar-multiples', [InsumoController::class, 'destroyMultiple'])->name('insumos.destroyMultiple');

//Rutas para TiposInsumo
Route::resource('tipo-insumos', TipoInsumoController::class);
