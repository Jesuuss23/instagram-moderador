<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_actividad', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('id_usuario');
            $table->string('accion', 100);
            $table->text('descripcion');
            $table->timestamp('fecha_registro')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_actividad');
    }
};