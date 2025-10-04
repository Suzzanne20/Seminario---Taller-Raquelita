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
        'detalles_json',        // 👈 añadir
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'detalles_json'  => 'array',   // 👈 se lee/escribe como array
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'recepcion_id', 'id');
    }
}
