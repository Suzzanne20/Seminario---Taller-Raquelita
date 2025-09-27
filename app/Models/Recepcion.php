<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    use HasFactory;

    protected $table = 'recepcion';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'vehiculo_placa',
        'observaciones',
        'type_vehiculo_id',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];

    // Relaciones útiles
    public function vehiculo()
    {
        // FK: recepcion.vehiculo_placa -> vehiculo.placa (owner key)
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'recepcion_id', 'id');
    }
}
