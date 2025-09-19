<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Marca; 

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculo';
    protected $primaryKey = 'placa';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'placa', 'modelo', 'linea', 'motor', 'cilindraje'
    ];

    
    public function marca()
    {
        return $this->hasOne(Marca::class, 'vehiculo_placa', 'placa');
    }
}
