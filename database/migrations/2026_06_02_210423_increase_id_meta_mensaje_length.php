<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Primero eliminar el índice único existente
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropUnique('mensajes_id_meta_mensaje_unique');
        });
        
        // Luego modificar la columna
        Schema::table('mensajes', function (Blueprint $table) {
            $table->string('id_meta_mensaje', 255)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropUnique('mensajes_id_meta_mensaje_unique');
            $table->string('id_meta_mensaje', 100)->unique()->change();
        });
    }
};