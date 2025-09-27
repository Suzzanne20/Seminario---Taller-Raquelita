<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'foto';   // <- la tabla es singular
    public $timestamps = false;

    protected $fillable = [
        'path_foto',
        'descripcion',
        'recepcion_id',
    ];
}
