<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/webhook-test', function (Request $request) {
    $verifyToken = 'mi_token_secreto_guando_2026';

    if ($request->query('hub_mode') === 'subscribe' && $request->query('hub_verify_token') === $verifyToken) {
        Log::info('Validando Webhook desde web.php con éxito');
        echo $request->query('hub_challenge');
        exit;
    }

    return response('Token inválido', 403);
});

Route::post('/webhook-test', function (Request $request) {
    Log::info('POST recibido en web.php:', $request->all());
    return response('EVENT_RECEIVED', 200);
});