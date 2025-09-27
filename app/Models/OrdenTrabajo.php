<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class OrdenTrabajo extends Model
{
    protected $table = 'orden_trabajo';
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'descripcion',
        'kilometraje',
        'proximo_servicio',
        'costo_mo',
        'total',
        'id_creador',
        'vehiculo_placa',
        'type_service_id',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo'       => 'decimal:2',
        'total'          => 'decimal:2',
        'vehiculo_placa' => 'string',
    ];

    // ------ Relaciones -------
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function tipoServicio(): BelongsTo
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    public function insumos(): HasMany
    {
        return $this->hasMany(InsumoOt::class, 'orden_trabajo_id');
    }

    public function estadoActual(): HasOne
    {
        return $this->hasOne(EstadoOrden::class, 'orden_trabajo_id')->latestOfMany('id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionOrden::class, 'orden_trabajo_id');
    }
}
