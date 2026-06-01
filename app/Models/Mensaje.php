<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    protected $primaryKey = 'id_mensaje';
    
    protected $fillable = [
        'id_conversacion', 'id_meta_mensaje', 'remitente_tipo', 
        'id_usuario_asesor', 'tipo_contenido', 'texto_mensaje', 
        'media_url', 'fecha_envio'
    ];
    
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'id_conversacion', 'id_conversacion');
    }
    
    public function asesor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_asesor', 'id_usuario');
    }
}