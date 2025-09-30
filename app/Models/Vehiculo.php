<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marca;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculo';
    protected $primaryKey = 'placa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'placa', 'modelo', 'linea', 'motor', 'cilindraje', 'marca_id'
    ];

    // Setter para convertir la placa en mayúsculas
    public function setPlacaAttribute($value)
    {
        $this->attributes['placa'] = strtoupper(trim($value));
    }

    // Relación con órdenes de trabajo
    public function ordenes()
    {
        return $this->hasMany(OrdenTrabajo::class, 'vehiculo_placa', 'placa');
    }

    // Relación muchos a muchos con clientes
    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'cliente_vehiculo', 'vehiculo_placa', 'cliente_id');
    }

    // Relación con la marca
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }
}
