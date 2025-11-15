<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumo';

    protected $fillable = [
        'codigo',
        'nombre',
        'costo',
        'stock',
        'stock_minimo',
        'descripcion',
        'type_insumo_id',
        'precio'
    ];

    public $timestamps = false;

    public function setCodigoAttribute($v)
    {
        $s = preg_replace('/\D+/', '', (string)$v);
        $this->attributes['codigo'] = str_pad($s, 4, '0', STR_PAD_LEFT);
    }

    public function tipoInsumo()
    {
        return $this->belongsTo(TipoInsumo::class, 'type_insumo_id');
    }

    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_insumo', 'insumo_id', 'cotizacion_id')
            ->withPivot('cantidad');
    }
}
