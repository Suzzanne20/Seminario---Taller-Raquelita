<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'descripcion',
        'costo_mo',
        'total',
        'type_service_id',
        'estado_id',
        // si más adelante agregas 'vehiculo_placa' o 'orden_trabajo_id', añádelos aquí
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function servicio()
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'cotizacion_insumo')
                    ->withPivot('cantidad');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function recalcularTotal()
    {
        $subtotalInsumos = $this->insumos->sum(fn($i) => $i->precio * $i->pivot->cantidad);
        $this->total = ($this->costo_mo ?? 0) + $subtotalInsumos;
    }
}
