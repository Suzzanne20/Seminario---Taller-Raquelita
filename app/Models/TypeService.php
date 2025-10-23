<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeService extends Model
{
    protected $table = 'type_service';
    public $timestamps = false;

    protected $fillable = ['descripcion'];

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'type_service_id');
    }

}
