<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrdenCompra2;

class OrdenCompraDetalle extends Model
{
    use HasFactory;

    // Nombre de la tabla si no sigue la convención de pluralización de Laravel
    protected $table = 'orden_compra_detalle';

    /**
     * Los atributos que son asignables masivamente.
     * Incluye todos los campos que se llenan con create() o update().
     */
    protected $fillable = [
        'orden_compra_id',
        'insumo_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    /**
     * Define la relación con la Orden de Compra padre.
     */
    public function ordenes()
    {
        return $this->belongsTo(OrdenCompra2::class, 'orden_compra_id');

    }

    /**
     * Define la relación con el Insumo.
     */
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
