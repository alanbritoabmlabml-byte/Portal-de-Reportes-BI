<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estructura de los reportes de Power BI: departamento → área → reporte,
 * más la tabla de quién puede VER cada reporte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('slug', 80)->unique();
            $table->string('descripcion', 200)->nullable();
            // Nombre del icono SVG (ver resources/views/components/icono.blade.php)
            $table->string('icono', 40)->default('edificio');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->string('nombre', 80);
            $table->string('slug', 80)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->string('icono', 40)->default('grafico');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->string('titulo', 120);
            $table->string('descripcion', 300)->nullable();
            $table->string('tipo', 20)->index();
            // URL de inserción de Power BI (la del botón Insertar → Sitio web o portal)
            $table->text('url_iframe');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            // publico = lo ve cualquier usuario con sesión; si no, solo los asignados
            $table->boolean('publico')->default(false);
            $table->timestamps();
        });

        // Accesos de vista: qué usuarios pueden ver un reporte que no es público
        Schema::create('reporte_user', function (Blueprint $table) {
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->primary(['reporte_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_user');
        Schema::dropIfExists('reportes');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('departamentos');
    }
};
