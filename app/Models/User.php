<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';   // ahora usa tu tabla
    protected $primaryKey = 'id';
    public $timestamps = false;

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
