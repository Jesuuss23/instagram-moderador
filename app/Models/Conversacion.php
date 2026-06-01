<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $table = 'conversaciones';
    protected $primaryKey = 'id_conversacion';
    
    protected $fillable = [
        'id_cuenta_ig', 'id_cliente_ig', 'ultimo_mensaje'
    ];
    
    public function cuentaInstagram()
    {
        return $this->belongsTo(CuentaInstagram::class, 'id_cuenta_ig', 'id_cuenta_ig');
    }
    
    public function clienteInstagram()
    {
        return $this->belongsTo(ClienteInstagram::class, 'id_cliente_ig', 'id_cliente_ig');
    }
    
    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'id_conversacion', 'id_conversacion');
    }
}