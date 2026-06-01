<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    
    protected $fillable = [
        'nombre', 'email', 'username', 'password', 'id_rol', 'activo'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'password' => 'hashed', // CRÍTICO: Para que Auth::attempt() funcione
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'id_usuario_asesor', 'id_usuario');
    }

    public function logs()
    {
        return $this->hasMany(LogActividad::class, 'id_usuario', 'id_usuario');
    }
}