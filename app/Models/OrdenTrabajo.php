<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

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
        'estado_id', // 👈 NECESARIO si la relación es directa
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo'       => 'decimal:2',
        'total'          => 'decimal:2',
        'vehiculo_placa' => 'string',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    public function insumos(): HasMany
    {
        return $this->hasMany(InsumoOt::class, 'orden_trabajo_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionOrden::class, 'orden_trabajo_id');
    }

    // Relación directa al estado actual
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }
}
