<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\CuentaInstagram;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $totalUsuarios = Usuario::count();
        $totalCuentas = CuentaInstagram::count();
        $totalConversaciones = Conversacion::count();
        $totalMensajes = Mensaje::count();
        
        // Estadísticas adicionales
        $usuariosActivos = Usuario::where('activo', 1)->count();
        $cuentasActivas = CuentaInstagram::where('activo', 1)->count();
        $conversacionesHoy = Conversacion::whereDate('created_at', today())->count();
        $mensajesHoy = Mensaje::whereDate('created_at', today())->count();
        
        // Mensajes no leídos (por implementar)
        $mensajesNoLeidos = 0;
        
        // Últimas conversaciones
        $ultimasConversaciones = Conversacion::with(['clienteInstagram', 'cuentaInstagram'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        // Mensajes recientes
        $mensajesRecientes = Mensaje::with(['conversacion.clienteInstagram', 'asesor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalUsuarios', 'totalCuentas', 'totalConversaciones', 'totalMensajes',
            'usuariosActivos', 'cuentasActivas', 'conversacionesHoy', 'mensajesHoy',
            'mensajesNoLeidos', 'ultimasConversaciones', 'mensajesRecientes'
        ));
    }
    
    public function estadisticas()
    {
        return view('admin.estadisticas.index');
    }
    public function actividadSemanal()
{
    $dias = [];
    $mensajesPorDia = [];
    $conversacionesPorDia = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $fecha = now()->subDays($i);
        $dias[] = $fecha->locale('es')->shortDayName;
        
        $mensajesPorDia[] = Mensaje::whereDate('created_at', $fecha)->count();
        $conversacionesPorDia[] = Conversacion::whereDate('created_at', $fecha)->count();
    }
    
    return response()->json([
        'dias' => $dias,
        'mensajes' => $mensajesPorDia,
        'conversaciones' => $conversacionesPorDia
    ]);
}
}