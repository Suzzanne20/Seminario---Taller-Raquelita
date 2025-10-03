<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'foto';
    public $timestamps = false;

    protected $fillable = [
        'path_foto',     // aquí va el binario (MEDIUMBLOB)
        'descripcion',
        'recepcion_id',
    ];
}
