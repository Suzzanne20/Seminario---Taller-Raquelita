<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    protected $table = 'recepcion';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'vehiculo_placa',
        'observaciones',
        'type_vehiculo_id',
        'detalles_json',
        'id_tecnico',        // <-- importante
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'detalles_json'  => 'array',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class, 'recepcion_id', 'id');
    }

    // 👇 ESTA ES LA RELACIÓN QUE FALTA
    public function tecnicoRel()
    {
        return $this->belongsTo(User::class, 'id_tecnico', 'id');
    }
}
