<?php

namespace App\Helpers;

use App\Models\ClienteInstagram;
use App\Models\Conversacion;
use App\Models\CuentaInstagram;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WebhookHelper
{
public static function processMessage($event)
{
    try {
        // 1. SI ES UN ECHO (Mensaje enviado por ti desde el panel o el cel), LO IGNORAMOS
        if (isset($event['message']['is_echo']) && $event['message']['is_echo'] === true) {
            Log::info('Webhook: Eco detectado e ignorado (Evita duplicar lo que tú envías).');
            return;
        }

        // 2. SI ES UN EVENTO DE LECTURA (read), LO IGNORAMOS
        if (isset($event['read'])) {
            Log::info('Webhook: Confirmación de lectura ignorada.');
            return;
        }

        // 3. SI ES UN EVENTO DE EDICIÓN O ESTADO (message_edit), LO IGNORAMOS
        if (isset($event['message_edit'])) {
            Log::info('Webhook: Evento message_edit ignorado.');
            return;
        }

        // --- A partir de aquí tu código procesa SOLO los mensajes reales de los clientes ---
        $senderId = $event['sender']['id'] ?? null;
        $recipientId = $event['recipient']['id'] ?? null;
        $timestamp = $event['timestamp'] ?? time() * 1000;
        
        if (!$senderId || !$recipientId) {
            Log::warning('Evento de Webhook mal formado: faltan IDs estructurales', ['event' => $event]);
            return;
        }
            
            // 1. Buscar la cuenta de Instagram (la que recibe el mensaje)
            $cuenta = CuentaInstagram::where('instagram_id_meta', $recipientId)->first();
            
            if (!$cuenta) {
                Log::warning('Cuenta no encontrada', ['instagram_id' => $recipientId]);
                return;
            }
            
            // 2. Buscar si el cliente ya existe en nuestra Base de Datos
            $cliente = ClienteInstagram::where('id_meta_cliente', $senderId)->first();
            
            // Si el cliente no existe, o si existe pero su username quedó como 'desconocido'
            if (!$cliente || $cliente->username_cliente === 'desconocido') {
                
                // Valores por defecto en caso de que falle la API de Meta
                $username = 'desconocido';
                $nombreCompleto = null;
                $fotoUrl = null;
                
                try {
                    // Consultamos el perfil del cliente a Meta usando tu token eterno de página
                    $profileResponse = Http::get("https://graph.facebook.com/v25.0/{$senderId}", [
                        'fields' => 'username,name,profile_pic',
                        'access_token' => $cuenta->access_token_page
                    ]);
                    
                    if ($profileResponse->successful()) {
                        $profileData = $profileResponse->json();
                        $username = $profileData['username'] ?? 'desconocido';
                        $nombreCompleto = $profileData['name'] ?? null;
                        $fotoUrl = $profileData['profile_pic'] ?? null;
                    } else {
                        Log::warning('Meta no devolvió un perfil exitoso', ['response' => $profileResponse->json()]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error consultando el perfil del cliente en Meta: ' . $e->getMessage());
                }
                
                // Creamos o actualizamos el cliente con los datos reales de Meta
                $cliente = ClienteInstagram::updateOrCreate(
                    ['id_meta_cliente' => $senderId],
                    [
                        'username_cliente' => $username,
                        'nombre_completo'  => $nombreCompleto,
                        'foto_cliente_url' => $fotoUrl
                    ]
                );
            }
            
            // 3. Buscar o crear la conversación
            $conversacion = Conversacion::firstOrCreate(
                [
                    'id_cuenta_ig' => $cuenta->id_cuenta_ig,
                    'id_cliente_ig' => $cliente->id_cliente_ig
                ]
            );
            
            // 4. Procesar el mensaje
            if (isset($event['message'])) {
                $message = $event['message'];
                $messageId = $message['mid'] ?? 'local_' . time();
                
                // Evitar duplicados
                if (Mensaje::where('id_meta_mensaje', $messageId)->exists()) {
                    Log::info('Mensaje duplicado ignorado', ['message_id' => $messageId]);
                    return;
                }
                
                // Determinar tipo de contenido
                $tipoContenido = 'text';
                $textoMensaje = null;
                $mediaUrl = null;
                
                if (isset($message['text'])) {
                    $textoMensaje = $message['text'];
                } elseif (isset($message['attachments'])) {
                    $attachment = $message['attachments'][0];
                    $tipoContenido = $attachment['type']; // image, video, audio
                    $mediaUrl = $attachment['payload']['url'] ?? null;
                }
                
                // Guardar mensaje
                Mensaje::create([
                    'id_conversacion' => $conversacion->id_conversacion,
                    'id_meta_mensaje' => $messageId,
                    'remitente_tipo' => 'CLIENTE',
                    'id_usuario_asesor' => null,
                    'tipo_contenido' => $tipoContenido,
                    'texto_mensaje' => $textoMensaje,
                    'media_url' => $mediaUrl,
                    'fecha_envio' => date('Y-m-d H:i:s', $timestamp / 1000)
                ]);
                
                // Actualizar último mensaje de la conversación
                $conversacion->update([
                    'ultimo_mensaje' => $textoMensaje ?? "[$tipoContenido]",
                    'updated_at' => now()
                ]);
                
                Log::info('Mensaje guardado correctamente', [
                    'conversacion_id' => $conversacion->id_conversacion,
                    'cliente_username' => $cliente->username_cliente,
                    'tipo' => $tipoContenido
                ]);
            }
            
            // 5. Procesar respuestas a historias (story_mention)
            if (isset($event['story_mention'])) {
                Log::info('Story mention recibido', $event['story_mention']);
            }
            
        } catch (\Exception $e) {
            Log::error('Error procesando webhook: ' . $e->getMessage(), [
                'event' => $event
            ]);
        }
    }
}