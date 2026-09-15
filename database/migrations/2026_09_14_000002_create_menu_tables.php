<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarjetas del menú principal (accesos a otros sitios), favoritos anclados
 * por cada usuario y notificaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('descripcion', 200)->nullable();
            $table->string('url', 500);
            // Texto del botón principal ("Ingresar", "Abrir sitio"...)
            $table->string('texto_boton', 40)->default('Ingresar');
            // Imagen de vista previa: ruta bajo public/ o URL absoluta
            $table->string('imagen', 300)->nullable();
            // Tono de la tarjeta: azul | rojo | tinta | claro
            $table->string('tono', 20)->default('azul');
            // Rótulo que acompaña la tarjeta (Interno, Público, Microsoft 365...)
            $table->string('etiqueta', 40)->nullable();
            $table->boolean('nueva_pestana')->default(true);
            // Preview destacado: la tarjeta ocupa el doble de ancho (portafolio)
            $table->boolean('destacado')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Favoritos polimórficos: una tarjeta (Acceso) o un área de BI (Area)
        Schema::create('favoritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('favorito');
            $table->timestamps();
            $table->unique(['user_id', 'favorito_type', 'favorito_id']);
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->string('mensaje', 500)->nullable();
            $table->string('tipo', 20)->default('info');
            $table->string('url', 500)->nullable();
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'leida_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('favoritos');
        Schema::dropIfExists('accesos');
    }
};
