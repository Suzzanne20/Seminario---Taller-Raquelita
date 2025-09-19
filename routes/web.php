<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\TipoInsumoController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

//Route::get('/dashboard', function () {
    //return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

//Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('clientes', ClienteController::class);
    Route::get('/ordenes',  [OrdenTrabajoController::class, 'index'])->name('ordenes.index');


//});



//require __DIR__.'/auth.php';//

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');




















































//Rutas para Insumos
Route::resource('insumos', InsumoController::class);
Route::delete('insumos/eliminar-multiples', [InsumoController::class, 'destroyMultiple'])->name('insumos.destroyMultiple');

//Rutas para TiposInsumo
Route::resource('tipo-insumos', TipoInsumoController::class);
