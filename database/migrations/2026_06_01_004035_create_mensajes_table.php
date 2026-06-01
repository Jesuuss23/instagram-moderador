<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id('id_mensaje');
            $table->unsignedBigInteger('id_conversacion');
            $table->string('id_meta_mensaje', 100)->unique();
            $table->enum('remitente_tipo', ['CUENTA', 'CLIENTE']);
            $table->unsignedBigInteger('id_usuario_asesor')->nullable();
            $table->enum('tipo_contenido', ['text', 'image', 'video', 'audio', 'story_mention']);
            $table->text('texto_mensaje')->nullable();
            $table->text('media_url')->nullable();
            $table->timestamp('fecha_envio');
            $table->timestamps();

            $table->foreign('id_conversacion')
                  ->references('id_conversacion')
                  ->on('conversaciones')
                  ->onDelete('restrict');

            $table->foreign('id_usuario_asesor')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};