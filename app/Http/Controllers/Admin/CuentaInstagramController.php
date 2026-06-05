<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaInstagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CuentaInstagramController extends Controller
{
    protected $version;

    public function __construct()
    {
        // Lee dinámicamente la versión del .env (v25.0)
        $this->version = env('META_API_VERSION', 'v25.0');
    }

    // Listar cuentas conectadas
    public function index()
    {
        $cuentas = CuentaInstagram::all();
        return view('admin.cuentas.index', compact('cuentas'));
    }

    public function conectar()
    {
        $facebookAppId = env('META_APP_ID'); 
        $redirectUri = env('META_REDIRECT_URI');
        
        $authUrl = "https://www.facebook.com/{$this->version}/dialog/oauth?" . http_build_query([
            'client_id'     => $facebookAppId,
            'redirect_uri' => $redirectUri,
            'response_type'=> 'code',
            // SCOPES CORREGIDOS: Agregado instagram_manage_messages para poder controlar los chats
            'scope'        => 'instagram_basic,instagram_manage_comments,instagram_manage_messages,pages_manage_metadata,pages_manage_posts,pages_messaging,pages_read_engagement,pages_show_list',
            
            'auth_type'    => 'rerequest'
        ]);
        
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        
        if (!$code) {
            return redirect()->route('admin.cuentas')->with('error', 'No se recibió código de autorización');
        }
        
        try {
            // 1. Intercambiar código por el Token de Usuario Original
            $response = Http::post("https://graph.facebook.com/{$this->version}/oauth/access_token", [
                'client_id' => env('META_APP_ID'),
                'client_secret' => env('META_APP_SECRET'),
                'redirect_uri' => env('META_REDIRECT_URI'),
                'code' => $code,
            ]);
            
            $data = $response->json();
            
            if (isset($data['error'])) {
                throw new \Exception($data['error']['message'] ?? 'Error al obtener token');
            }
            
            $accessToken = $data['access_token'];
            
            // 2. ¡EL CAMBIO CLAVE! Traemos las páginas e inyectamos la búsqueda de Instagram en un solo viaje
            $pagesResponse = Http::get("https://graph.facebook.com/{$this->version}/me/accounts", [
                'access_token' => $accessToken,
                'fields' => 'id,name,access_token,instagram_business_account{id,username}' 
            ]);
            
            $pages = $pagesResponse->json();
            Log::info('PRUEBA DIRECTA PAGES RESPONSE CON IG: ' . json_encode($pages));
            
            if (empty($pages['data'])) {
                throw new \Exception('Meta sigue retornando el array vacío en el token directo.');
            }
            
            $instagramVinculado = false;

            foreach ($pages['data'] as $page) {
                $pageId = $page['id'];
                $pageToken = $page['access_token']; 
                
                // Si la página viene con el nodo de Instagram Business estructurado
                if (isset($page['instagram_business_account']['id'])) {
                    $instagramId = $page['instagram_business_account']['id'];
                    $instagramUsername = $page['instagram_business_account']['username'] ?? 'desconocido';
                    
                    // 3. Consultamos el perfil para extraer detalles estéticos (Foto y Nombre Comercial)
                    $profileResponse = Http::get("https://graph.facebook.com/{$this->version}/{$instagramId}", [
                        'fields' => 'name,profile_picture_url',
                        'access_token' => $pageToken
                    ]);
                    
                    $profile = $profileResponse->json();
                    
                    // 4. Guardamos o actualizamos de forma automatizada en tu MySQL
                    CuentaInstagram::updateOrCreate(
                        ['instagram_id_meta' => $instagramId],
                        [
                            'username_ig' => $instagramUsername,
                            'nombre_cuenta' => $profile['name'] ?? $instagramUsername,
                            'foto_perfil_url' => $profile['profile_picture_url'] ?? null,
                            'facebook_page_id' => $pageId,
                            'access_token_page' => $pageToken, // Tu token eterno e indestructible
                            'activo' => 1,
                        ]
                    );

                    $instagramVinculado = true;
                    // Si solo quieres vincular la primera cuenta que encuentres, dejamos el break.
                    // Si quieres vincular todas de golpe, simplemente borra la línea de abajo.
                    break; 
                }
            }
            
            if (!$instagramVinculado) {
                throw new \Exception('Ninguna página tiene el Instagram Business correctamente amarrado.');
            }
            
            return redirect()->route('admin.cuentas')->with('success', '¡Conectado exitosamente!');
            
        } catch (\Exception $e) {
            Log::error('Error al conectar Instagram: ' . $e->getMessage());
            return redirect()->route('admin.cuentas')->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }

    // Desconectar cuenta
    public function destroy($id)
    {
        $cuenta = CuentaInstagram::findOrFail($id);
        
        if ($cuenta->conversaciones()->count() > 0) {
            $cuenta->update(['activo' => 0]);
            return redirect()->route('admin.cuentas')->with('warning', 'La cuenta tiene conversaciones activas. Se ha desactivado en lugar de eliminar.');
        }
        
        $cuenta->delete();
        return redirect()->route('admin.cuentas')->with('success', 'Cuenta desconectada correctamente');
    }
    
    // Reactivar cuenta desactivada
    public function reactivar($id)
    {
        $cuenta = CuentaInstagram::findOrFail($id);
        $cuenta->update(['activo' => 1]);
        return redirect()->route('admin.cuentas')->with('success', 'Cuenta reactivada correctamente');
    }
}