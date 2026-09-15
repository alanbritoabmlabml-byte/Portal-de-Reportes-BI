<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Deja la base lista para servir: crea el archivo SQLite si hace falta, corre
 * las migraciones y, solo si no hay usuarios, carga los datos de ejemplo.
 *
 * Lo llama docker/entrypoint.sh en cada arranque del contenedor, así que
 * tiene que ser idempotente: en una base ya poblada no toca nada.
 */
class PrepararPortal extends Command
{
    protected $signature = 'portal:preparar {--sin-datos : No cargar los datos de ejemplo aunque la base esté vacía}';

    protected $description = 'Crea/migra la base y la siembra con datos de ejemplo si está vacía';

    public function handle(): int
    {
        if (config('database.default') === 'sqlite') {
            $ruta = config('database.connections.sqlite.database');

            if ($ruta !== ':memory:' && ! file_exists($ruta)) {
                @mkdir(dirname($ruta), 0775, true);
                touch($ruta);
                $this->info("Base SQLite creada en {$ruta}");
            }
        }

        $this->call('migrate', ['--force' => true, '--no-interaction' => true]);

        if ($this->option('sin-datos')) {
            return self::SUCCESS;
        }

        if (Schema::hasTable('users') && User::query()->count() === 0) {
            $this->info('Base vacía: cargando datos de ejemplo.');
            Artisan::call('db:seed', ['--force' => true, '--no-interaction' => true], $this->getOutput());
        } else {
            $this->line('La base ya tiene usuarios: no se cargan datos de ejemplo.');
        }

        return self::SUCCESS;
    }
}
