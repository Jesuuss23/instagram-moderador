<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversaciones', function (Blueprint $table) {
            $table->id('id_conversacion');
            $table->unsignedBigInteger('id_cuenta_ig');
            $table->unsignedBigInteger('id_cliente_ig');
            $table->text('ultimo_mensaje')->nullable();
            $table->timestamps();

            $table->foreign('id_cuenta_ig')
                  ->references('id_cuenta_ig')
                  ->on('cuentas_instagram')
                  ->onDelete('restrict');

            $table->foreign('id_cliente_ig')
                  ->references('id_cliente_ig')
                  ->on('clientes_instagram')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversaciones');
    }
};