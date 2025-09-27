<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'orden_compra'; // tu tabla real
    public $timestamps = false;

    protected $fillable = [
        'fecha_creacion',
        'descripcion',
        'total',
        'proveedor_id'
    ];
}
