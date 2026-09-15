<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v2: preferencia de apariencia por usuario y agrupación de las tarjetas del
 * menú en mosaicos (públicos, SharePoint, desarrollos propios, Microsoft 365).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tema', 10)->default('sistema')->after('activo');
        });

        Schema::table('accesos', function (Blueprint $table) {
            $table->string('grupo', 20)->default('propio')->after('etiqueta')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tema');
        });

        Schema::table('accesos', function (Blueprint $table) {
            $table->dropColumn('grupo');
        });
    }
};
