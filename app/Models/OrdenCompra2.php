<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Proveedor; // Asegúrate de importar el modelo Proveedor

class OrdenCompra2 extends Model
{
    // La tabla que utiliza el modelo
    protected $table = 'orden_compra';

    // Campos que permiten la asignación masiva
    protected $fillable = [
        'numero_orden',
        'fecha_orden',
        'fecha_entrega_esperada',
        'proveedor_id',
        'estado',
        'total',
        'observaciones',
        'orden_trabajo_id',
    ];

    /**
     * Define la relación: Una Orden de Compra pertenece a un Proveedor.
     */
    public function proveedor()
    {
        // Asume que la clave foránea es 'proveedor_id' en la tabla 'orden_compra'
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    // Opcional: Relación con los detalles, para completar el modelo
    public function detalles()
    {
        return $this->hasMany(OrdenCompraDetalle::class, 'orden_compra_id');
    }
    public function ordenTrabajo()
    {
        return $this->belongsTo(\App\Models\OrdenTrabajo::class, 'orden_trabajo_id');
    }

}
