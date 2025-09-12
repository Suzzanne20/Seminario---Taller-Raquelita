<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;
    public function clientes() {
        return $this->belongsToMany(Cliente::class, 'cliente_vehiculo', 'vehiculo_placa', 'cliente_id');
    }

}
