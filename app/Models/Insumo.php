<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumo';

    protected $fillable = [
        'nombre',
        'costo',
        'stock',
        'stock_minimo',
        'descripcion',
        'type_insumo_id',
        'precio'
    ];

    public $timestamps = false;

    public function tipoInsumo()
    {
        return $this->belongsTo(TipoInsumo::class, 'type_insumo_id');
    }
}
