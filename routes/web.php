<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\CotizacionController;

Route::get('/', function () {
    return view('welcome');
});

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

// Autenticación
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
