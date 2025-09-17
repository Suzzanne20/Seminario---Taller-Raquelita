<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $table = 'insumo';
    public $timestamps = false;

    protected $fillable = ['nombre','costo','stock','stock_minimo','descripcion','type_insumo_id','precio'];

    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_insumo', 'insumo_id', 'cotizacion_id')
            ->withPivot('cantidad');
    }
}
