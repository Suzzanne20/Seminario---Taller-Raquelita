<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizacion';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'descripcion',
        'costo_mo',
        'total',
        'type_service_id',
        'estado'
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
        return $this->belongsToMany(
            Insumo::class,
            'cotizacion_insumo',
            'cotizacion_id',
            'insumo_id'
        )->withPivot('cantidad');
    }

    public function recalcularTotal(): float
    {
        $subtotalInsumos = $this->insumos->sum(function ($insumo) {
            return (float)$insumo->precio * (float)$insumo->pivot->cantidad;
        });

        $total = (float)$subtotalInsumos + (float)($this->costo_mo ?? 0);
        $this->total = $total;
        return $total;
    }
}
