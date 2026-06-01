<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| 1. RUTAS DE AUTENTICACIÓN (LOGIN & LOGOUT)
|--------------------------------------------------------------------------
*/
// Mostrar el formulario (Ambas URLs cargan la vista)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm']);

// Procesar el envío de datos (Soporta el POST en la raíz y en /login)
Route::post('/', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login']);

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
/*
|--------------------------------------------------------------------------
| 2. RUTAS PROTEGIDAS (PANEL / MULTICHAT)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', function () {return 'Bienvenido ' . auth()->user()->nombre . ' - Rol: ' . auth()->user()->rol->nombre_role;
    })->name('dashboard');
    // Panel de Administrador
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('usuarios', App\Http\Controllers\Admin\UsuarioController::class);
        Route::post('/usuarios/{id}/toggle', [App\Http\Controllers\Admin\UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle');
    });
    
    // Multichat (temporal)
    Route::get('/multichat', function () {
        return 'Panel de Multichat - Usuario: ' . auth()->user()->nombre;
    })->name('multichat.index');
});

/*
|--------------------------------------------------------------------------
| 3. ENDPOINT DEL WEBHOOK DE META (INSTAGRAM)
|--------------------------------------------------------------------------
| ¡NO BORRAR! Este bloque mantiene vivo el puente de comunicación con Meta.
*/
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