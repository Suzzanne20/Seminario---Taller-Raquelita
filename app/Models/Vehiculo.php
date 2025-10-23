<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marca;
use App\Models\OrdenTrabajo;
use App\Models\Cliente;

class Vehiculo extends Model
{
    use HasFactory;

    // Nombre de la tabla (ya que no sigue la convención plural)
    protected $table = 'vehiculo';

    // Clave primaria personalizada
    protected $primaryKey = 'placa';
    public $incrementing = false; 
    protected $keyType = 'string';
    public $timestamps = false;

    // Campos que pueden asignarse masivamente (fillable)
    protected $fillable = [
        'placa',
        'marca_id',
        'modelo',
        'linea',
        'motor',
        'cilindraje',
        'type_vehiculo_id',

        // 🔽 Campos nuevos (según migración opción B)
        'cantidad_aceite_motor',
        'marca_aceite',
        'tipo_aceite',
        'filtro_aceite',
        'filtro_aire',
        'cantidad_aceite_cc',
        'marca_cc',
        'tipo_aceite_cc',
        'filtro_aceite_cc',
        'filtro_de_enfriador',
        'tipo_caja',
        'cantidad_aceite_diferencial',
        'marca_aceite_d',
        'tipo_aceite_d',
        'cantidad_aceite_transfer',
        'marca_aceite_t',
        'tipo_aceite_t',
        'filtro_cabina',
        'filtro_diesel',
        'contra_filtro_diesel',
        'candelas',
        'pastillas_delanteras',
        'pastillas_traseras',
        'fajas',
        'aceite_hidraulico',
    ];

    // Mutator para asegurar que la placa se guarde en mayúsculas
    public function setPlacaAttribute($value)
    {
        $this->attributes['placa'] = strtoupper(trim($value));
    }

    // 🔗 Relaciones

    // Relación con órdenes de trabajo
    public function ordenes()
    {
        return $this->hasMany(OrdenTrabajo::class, 'vehiculo_placa', 'placa');
    }

    // Relación muchos a muchos con clientes
    public function clientes()
    {
        return $this->belongsToMany(
            Cliente::class,
            'cliente_vehiculo',
            'vehiculo_placa',
            'cliente_id',
            'placa',
            'id'
        );
    }

    // Relación con la marca
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }
}
