<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marca; 

class Vehiculo extends Model
{

    protected $table = 'vehiculo';
    protected $primaryKey = 'placa';
    public $incrementing = false; 
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'placa', 'modelo', 'linea', 'motor', 'cilindraje', 'marca_id'
    ];

    public function setPlacaAttribute($value)
    {
        $this->attributes['placa'] = strtoupper(trim($value));
    }

    use HasFactory;

        public function ordenes()
    {
        return $this->hasMany(OrdenTrabajo::class, 'vehiculo_placa', 'placa');
    }
    
    public function clientes() {
        return $this->belongsToMany(Cliente::class, 'cliente_vehiculo', 'vehiculo_placa', 'cliente_id');
    }


    protected $table = 'vehiculo';
    protected $primaryKey = 'placa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'placa', 'modelo', 'linea', 'motor', 'cilindraje'
    ];

    
    public function marca()
    {
        return $this->hasOne(Marca::class, 'vehiculo_placa', 'placa');
    }
}
