<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoInsumo extends Model
{
    use HasFactory;

    // Define el nombre de la tabla
    protected $table = 'type_insumo';

    // Deshabilita los timestamps si no los tienes en la tabla
    public $timestamps = false;

    // Campos que pueden ser llenados masivamente
    protected $fillable = [
        'nombre'
    ];
}
