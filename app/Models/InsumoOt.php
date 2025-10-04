<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoOt extends Model
{
    protected $table = 'insumo_ot';
    public $timestamps = false;
    protected $fillable = ['orden_trabajo_id','insumo_id','cantidad'];

    public function insumo() { return $this->belongsTo(Insumo::class, 'insumo_id'); }
}
