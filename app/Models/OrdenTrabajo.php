<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'orden_trabajo';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion','descripcion','costo_mo','total',
        'type_service_id','empleado_id','cotizacion_id'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function servicio()
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }
}
