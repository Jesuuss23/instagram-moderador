<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaInstagram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EstadisticasController extends Controller
{
    public function index()
    {
        $cuentas = CuentaInstagram::where('activo', 1)->get();
        return view('admin.estadisticas.index', compact('cuentas'));
    }
    
    public function getEstadisticas($cuentaId)
    {
        try {
            $cuenta = CuentaInstagram::findOrFail($cuentaId);
            $token = $cuenta->access_token_page;
            $instagramId = $cuenta->instagram_id_meta;
            
            $profileStats = $this->getProfileStats($instagramId, $token);
            $posts = $this->getRecentPosts($instagramId, $token);
            
            $topLikes = collect($posts)->sortByDesc('like_count')->take(5)->values();
            $topComments = collect($posts)->sortByDesc('comments_count')->take(5)->values();
            $topImpressions = collect($posts)->sortByDesc('impressions')->take(5)->values();
            
            return response()->json([
                'success' => true,
                'profile' => $profileStats,
                'posts' => $posts,
                'rankings' => [
                    'top_likes' => $topLikes,
                    'top_comments' => $topComments,
                    'top_impressions' => $topImpressions
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function getProfileStats($instagramId, $token)
    {
        $response = Http::withToken($token)->get("https://graph.facebook.com/v25.0/{$instagramId}", [
            'fields' => 'id,username,name,profile_picture_url,followers_count,follows_count,media_count,biography'
        ]);
        
        $data = $response->json();
        
        $insightsResponse = Http::withToken($token)->get("https://graph.facebook.com/v25.0/{$instagramId}/insights", [
            'metric' => 'impressions,reach,website_clicks',
            'period' => 'day',
            'since' => now()->subDays(30)->timestamp,
            'until' => now()->timestamp
        ]);
        
        $insights = $insightsResponse->json();
        
        return [
            'username' => $data['username'] ?? 'N/A',
            'name' => $data['name'] ?? 'N/A',
            'profile_picture' => $data['profile_picture_url'] ?? null,
            'followers' => $data['followers_count'] ?? 0,
            'following' => $data['follows_count'] ?? 0,
            'total_posts' => $data['media_count'] ?? 0,
            'biography' => $data['biography'] ?? '',
            'insights' => $this->parseInsights($insights)
        ];
    }
    
    private function getRecentPosts($instagramId, $token)
    {
        $response = Http::withToken($token)->get("https://graph.facebook.com/v25.0/{$instagramId}/media", [
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'limit' => 15
        ]);
        
        $data = $response->json();
        $posts = [];
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $post) {
                $impressions = 0;
                $reach = 0;
                $saved = 0;
                $plays = 0;
                $shares = 0;
                
                try {
                    if ($post['media_type'] === 'VIDEO') {
                        $insightsResponse = Http::withToken($token)->get("https://graph.facebook.com/v25.0/{$post['id']}/insights", [
                            'metric' => 'plays,shares,total_interactions'
                        ]);
                        
                        $insightData = $insightsResponse->json();
                        if (isset($insightData['data'])) {
                            foreach ($insightData['data'] as $metric) {
                                if ($metric['name'] === 'plays') $plays = $metric['values'][0]['value'] ?? 0;
                                if ($metric['name'] === 'shares') $shares = $metric['values'][0]['value'] ?? 0;
                            }
                        }
                    } else {
                        $insightsResponse = Http::withToken($token)->get("https://graph.facebook.com/v25.0/{$post['id']}/insights", [
                            'metric' => 'impressions,reach,saved'
                        ]);
                        
                        $insightData = $insightsResponse->json();
                        if (isset($insightData['data'])) {
                            foreach ($insightData['data'] as $metric) {
                                if ($metric['name'] === 'impressions') $impressions = $metric['values'][0]['value'] ?? 0;
                                if ($metric['name'] === 'reach') $reach = $metric['values'][0]['value'] ?? 0;
                                if ($metric['name'] === 'saved') $saved = $metric['values'][0]['value'] ?? 0;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("No se pudieron obtener insights para el post ID {$post['id']}: " . $e->getMessage());
                }
                
                $posts[] = [
                    'id' => $post['id'],
                    'caption' => $post['caption'] ?? '',
                    'media_type' => $post['media_type'],
                    'media_url' => $post['media_url'] ?? null,
                    'thumbnail_url' => $post['thumbnail_url'] ?? null,
                    'permalink' => $post['permalink'] ?? '#',
                    'timestamp' => $post['timestamp'],
                    'like_count' => $post['like_count'] ?? 0,
                    'comments_count' => $post['comments_count'] ?? 0,
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'saved' => $saved,
                    'plays' => $plays,
                    'shares' => $shares,
                    'engagement' => ($post['like_count'] ?? 0) + ($post['comments_count'] ?? 0) + $saved
                ];
            }
        }
        
        usort($posts, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return $posts;
    }
    
    private function parseInsights($insights)
    {
        $result = [];
        if (isset($insights['data'])) {
            foreach ($insights['data'] as $metric) {
                $values = $metric['values'] ?? [];
                $total = 0;
                foreach ($values as $value) {
                    $total += $value['value'] ?? 0;
                }
                $result[$metric['name']] = $total;
            }
        }
        return $result;
    }
}
