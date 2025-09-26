<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones'; // 👈 plural
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'descripcion',
        'costo_mo',
        'total',
        'type_service_id',
        'estado_id'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación con servicios
    public function servicio()
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    // Relación con insumos (pivot cotizacion_insumo)
    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'cotizacion_insumo')
            ->withPivot('cantidad');
    }

    // Relación con estado
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }


    // 🔽 Método que faltaba
    public function recalcularTotal()
    {
        $subtotalInsumos = $this->insumos->sum(function ($insumo) {
            return $insumo->precio * $insumo->pivot->cantidad;
        });

        $this->total = $subtotalInsumos + ($this->costo_mo ?? 0);
    }
}
