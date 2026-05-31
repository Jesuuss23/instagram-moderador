<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/webhook', function (Illuminate\Http\Request $request) {
    $verifyToken = 'mi_token_secreto_guando_2026';

    if ($request->query('hub_mode') === 'subscribe' && $request->query('hub_verify_token') === $verifyToken) {
        echo $request->query('hub_challenge');
        exit;
    }

    return response('Token inválido', 403);
});

Route::post('/webhook', function (Request $request) {
    $payload = $request->all();
    Log::info('Mensaje entrante de Instagram:', $payload);
    
    return response('EVENT_RECEIVED', 200);
});