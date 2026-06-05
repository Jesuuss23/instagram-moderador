<?php

namespace App\Http\Controllers;

use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Models\CuentaInstagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MultichatController extends Controller
{
    public function index()
    {
        $cuentas = CuentaInstagram::where('activo', 1)->get();
        return view('multichat.index', compact('cuentas'));
    }
    
    public function getConversaciones(Request $request)
    {
        $cuentaId = $request->get('cuenta_id');
        
        $conversaciones = Conversacion::with(['clienteInstagram', 'cuentaInstagram'])
            ->when($cuentaId, function ($query) use ($cuentaId) {
                return $query->where('id_cuenta_ig', $cuentaId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return response()->json($conversaciones);
    }
    
    public function getMensajes($conversacionId)
    {
        $mensajes = Mensaje::with('asesor')
            ->where('id_conversacion', $conversacionId)
            ->orderBy('fecha_envio', 'asc')
            ->get();
        
        return response()->json($mensajes);
    }
    
    public function enviarMensaje(Request $request)
    {
        $request->validate([
            'conversacion_id' => 'required|exists:conversaciones,id_conversacion',
            'mensaje' => 'required|string|max:2000',
        ]);
        
        try {
            $conversacion = Conversacion::findOrFail($request->conversacion_id);
            $cuenta = $conversacion->cuentaInstagram;
            $cliente = $conversacion->clienteInstagram;
            
        $body = [
                'recipient' => [
                    'id' => $cliente->id_meta_cliente 
                ],
                'message' => [
                    'text' => $request->mensaje 
                ]
            ];
            
            $response = Http::withToken($cuenta->access_token_page) 
                            ->post("https://graph.facebook.com/v25.0/me/messages", $body); 

            $result = $response->json();
            
            Log::info('RESPUESTA DE META AL ENVIAR:', $result);
            
            if (isset($result['error'])) {
                throw new \Exception($result['error']['message'] . " (Código: " . $result['error']['code'] . ")");
            }
            
            // Guardar mensaje enviado
            $mensaje = Mensaje::create([
                'id_conversacion' => $conversacion->id_conversacion,
                'id_meta_mensaje' => $result['message_id'] ?? 'local_' . time(),
                'remitente_tipo' => 'CUENTA',
                'id_usuario_asesor' => auth()->user()->id_usuario,
                'tipo_contenido' => 'text',
                'texto_mensaje' => $request->mensaje,
                'fecha_envio' => now(),
            ]);
            
            $conversacion->update([
                'ultimo_mensaje' => $request->mensaje,
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'mensaje' => $mensaje
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function marcarLeido($conversacionId)
    {
        return response()->json(['success' => true]);
    }

    // Enviar mensaje con imagen - CORREGIDO
public function enviarImagen(Request $request)
{
    $request->validate([
        'conversacion_id' => 'required|exists:conversaciones,id_conversacion',
        'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // max 5MB
    ]);
    
    try {
        $conversacion = Conversacion::findOrFail($request->conversacion_id);
        $cuenta = $conversacion->cuentaInstagram;
        $cliente = $conversacion->clienteInstagram;
        
        // 1. GUARDAR LA IMAGEN EN TU DISCO PÚBLICO PARA GENERAR UNA URL DE INTERNET
        // Esto creará un archivo en storage/app/public/instagram_media/nombre_unico.jpg
        $path = $request->file('imagen')->store('instagram_media', 'public');
        
        // Generamos la URL pública (ejemplo: https://instagram.donguando.com/storage/instagram_media/xyz.jpg)
        $publicUrl = asset('storage/' . $path);
        
        // 2. ARMAR EL BODY EXACTO CON LA URL PÚBLICA DE LA FOTO
        $body = [
            'recipient' => [
                'id' => $cliente->id_meta_cliente
            ],
            'message' => [
                'attachment' => [
                    'type' => 'image',
                    'payload' => [
                        'is_reusable' => false,
                        'url' => $publicUrl // <--- Ahora sí es un enlace que Meta puede leer
                    ]
                ]
            ]
        ];
        
        // 3. HACEMOS EL DISPARO ENVIANDO EL TOKEN EN LA CABECERA (BEARER)
        $response = Http::withToken($cuenta->access_token_page)
                        ->post("https://graph.facebook.com/v25.0/me/messages", $body);
        
        $result = $response->json();
        
        Log::info('RESPUESTA DE META ENVIAR IMAGEN:', $result);
        
        if (isset($result['error'])) {
            throw new \Exception($result['error']['message'] . " (Código: " . $result['error']['code'] . ")");
        }
        
        // 4. Guardar el registro en la base de datos local
        $mensaje = Mensaje::create([
            'id_conversacion' => $conversacion->id_conversacion,
            'id_meta_mensaje' => $result['message_id'] ?? 'img_' . time(),
            'remitente_tipo' => 'CUENTA',
            'id_usuario_asesor' => auth()->user()->id_usuario,
            'tipo_contenido' => 'image',
            'media_url' => $publicUrl, // Guardamos la URL real de la foto
            'fecha_envio' => now(),
        ]);
        
        $conversacion->update([
            'ultimo_mensaje' => '[Imagen]',
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'mensaje' => $mensaje
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error al enviar imagen: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}