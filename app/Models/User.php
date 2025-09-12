<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';   // ahora usa tu tabla
    protected $primaryKey = 'id';
    public $timestamps = false;     // tu tabla no tiene created_at / updated_at

    /**
     * El campo que se usará para login (antes era email).
     */
    public function getAuthIdentifierName()
    {
        return 'nombre';
    }

    /**
     * Campo que usará Auth::attempt()
     */
    public function username()
    {
        return 'nombre';
    }

    protected $fillable = [
        'nombre',
        'password',
        'role_id'
    ];

    protected $hidden = [
        'password',
    ];
}
