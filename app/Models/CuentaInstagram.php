<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaInstagram extends Model
{
    protected $table = 'cuentas_instagram';
    protected $primaryKey = 'id_cuenta_ig';
    
    protected $fillable = [
        'instagram_id_meta', 'username_ig', 'nombre_cuenta', 
        'foto_perfil_url', 'facebook_page_id', 'access_token_page', 'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
    ];
    
    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class, 'id_cuenta_ig', 'id_cuenta_ig');
    }
}