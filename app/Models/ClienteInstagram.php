<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteInstagram extends Model
{
    protected $table = 'clientes_instagram';
    protected $primaryKey = 'id_cliente_ig';
    
    protected $fillable = [
        'id_meta_cliente', 'username_cliente', 'nombre_completo', 'foto_cliente_url'
    ];
    
    public function conversaciones()
    {
        return $this->hasMany(Conversacion::class, 'id_cliente_ig', 'id_cliente_ig');
    }
}