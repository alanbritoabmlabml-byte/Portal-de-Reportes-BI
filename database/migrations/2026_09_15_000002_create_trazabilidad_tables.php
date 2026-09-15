<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trazabilidad: quién entró, cuánto duró su sesión, por qué páginas pasó,
 * cuánto tiempo estuvo en cada una y qué hizo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Id de sesión de Laravel: enlaza las visitas con el mismo inicio
            $table->string('session_id', 100)->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('navegador', 80)->nullable();
            $table->string('plataforma', 40)->nullable();
            $table->string('agente', 500)->nullable();
            $table->timestamp('iniciada_at');
            $table->timestamp('ultima_at');
            $table->timestamp('cerrada_at')->nullable();
            // salida (cerró sesión), expirada (se le venció), null = abierta
            $table->string('motivo_cierre', 20)->nullable();
            $table->unsignedInteger('segundos')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'iniciada_at']);
        });

        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->nullable()->constrained('sesiones')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ruta', 120)->nullable()->index();
            $table->string('url', 500);
            $table->string('titulo', 160);
            $table->timestamp('entrada_at');
            $table->timestamp('salida_at')->nullable();
            $table->unsignedInteger('segundos')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'entrada_at']);
        });

        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->nullable()->constrained('sesiones')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Se guarda el nombre por si la cuenta se elimina después
            $table->string('usuario_nombre', 120)->nullable();
            $table->string('accion', 20)->index();
            $table->string('entidad', 60)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('descripcion', 300);
            $table->json('datos')->nullable();
            $table->string('ruta', 120)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
        Schema::dropIfExists('visitas');
        Schema::dropIfExists('sesiones');
    }
};
