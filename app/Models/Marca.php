<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo; 

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marca';
    protected $fillable = ['nombre', 'activo', 'mostrar_en_registro'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'marca_id', 'id');
    }
    
    // Scope para marcas activas
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
    
    // Scope para marcas inactivas  
    public function scopeInactivas($query)
    {
        return $query->where('activo', false);
    }

    // Scope para marcas que se muestran en registro
    public function scopeMostrarEnRegistro($query)
    {
        return $query->where('mostrar_en_registro', true)
                    ->orWhere('activo', true);
    }
}