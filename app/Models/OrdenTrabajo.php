<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'estado_id',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'costo_mo'       => 'decimal:2',
        'total'          => 'decimal:2',
        'vehiculo_placa' => 'string',
    ];

    // --- Relaciones principales ---

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id');
    }

    public function insumos(): BelongsToMany
    {
        return $this->belongsToMany(Insumo::class, 'insumo_ot', 'orden_trabajo_id', 'insumo_id')
                    ->withPivot('cantidad');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\InsumoOt::class, 'orden_trabajo_id');
    }

    public function tecnicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'asignacion_orden', 'orden_trabajo_id', 'usuario_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'id_creador');
    }
}