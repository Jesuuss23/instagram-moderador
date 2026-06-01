<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Si usas tokens para APIs de JavaScript o flujos externos en el futuro:
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');