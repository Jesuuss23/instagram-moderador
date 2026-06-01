<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActividad extends Model
{
    protected $table = 'logs_actividad';
    protected $primaryKey = 'id_log';
    
    protected $fillable = [
        'id_usuario', 'accion', 'descripcion'
    ];
    
    public $timestamps = false;
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}