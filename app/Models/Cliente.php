<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'cliente'; // nombre real de tu tabla en MySQL
    protected $primaryKey = 'id';
    public $timestamps = false;   // tu tabla no tiene created_at / updated_at

    protected $fillable = [
        'nombre',
        'nit',
        'telefono',
        'direccion',
    ];

    // Relación con vehículos (si la quieres usar más adelante)
    public function vehiculos()
    {
        return $this->belongsToMany(Vehiculo::class, 'cliente_vehiculo', 'cliente_id', 'vehiculo_placa');
    }
}
