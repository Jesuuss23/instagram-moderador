<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes_instagram', function (Blueprint $table) {
            $table->id('id_cliente_ig');
            $table->string('id_meta_cliente', 50)->unique();
            $table->string('username_cliente', 100);
            $table->string('nombre_completo', 150)->nullable();
            $table->text('foto_cliente_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes_instagram');
    }
};