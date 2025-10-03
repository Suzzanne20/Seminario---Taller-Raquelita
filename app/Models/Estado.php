<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estado';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(OrdenTrabajo::class, 'estado_id', 'id');
    }

        public function getBadgeClassAttribute(): string
    {
        return match ($this->nombre) {
            'Nueva'      => 'secondary',
            'Asignada'   => 'info',
            'En proceso' => 'primary',
            'Pendiente'  => 'warning',
            'Finalizada' => 'success',
            'Aprobada'   => 'success',
            'Rechazada'  => 'danger',
            default      => 'dark',
        };
    }
}
