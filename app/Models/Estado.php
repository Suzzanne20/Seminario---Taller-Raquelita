<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    // Nombre de la tabla
    protected $table = 'estado';
    public $timestamps = false;

    // Campos que se pueden llenar en masa
    protected $fillable = [
        'nombre'
    ];

    /**
     * Relación: un estado puede pertenecer a muchas cotizaciones
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'estado_id');
    }
}
