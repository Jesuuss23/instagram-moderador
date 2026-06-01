<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_instagram', function (Blueprint $table) {
            $table->id('id_cuenta_ig');
            $table->string('instagram_id_meta', 50)->unique();
            $table->string('username_ig', 100);
            $table->string('nombre_cuenta', 150)->nullable();
            $table->text('foto_perfil_url')->nullable();
            $table->string('facebook_page_id', 50);
            $table->text('access_token_page');
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_instagram');
    }
};