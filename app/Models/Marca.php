<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo; 

class Marca extends Model
{
    use HasFactory;

    protected $table = 'marca';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'vehiculo_placa'
    ];

   
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_placa', 'placa');
    }
}
