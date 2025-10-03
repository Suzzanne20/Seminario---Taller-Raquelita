<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vehiculo; 

class Marca extends Model
{
    protected $table = 'marca';
    protected $fillable = ['nombre'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'marca_id', 'id');
    }
}
