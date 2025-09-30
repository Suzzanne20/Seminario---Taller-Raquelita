<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class EstadoOrden extends Model
{
    protected $table = 'estado_orden'; 
    public $timestamps = false;

    protected $fillable = ['estado_id', 'orden_trabajo_id'];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}