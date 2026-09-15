<?php

/**
 * Genera el .env del entorno local a partir de .env.example.
 *
 * Lo llama iniciar-reportes-bi.bat antes de arrancar. Si el .env ya existe no
 * lo toca, para no pisar ajustes hechos a mano.
 *
 * La diferencia con producción es la base: en local se usa sqlite, un solo
 * archivo en database/database.sqlite, así no hay que instalar ni arrancar
 * MySQL para levantar el proyecto. Sesión, caché y cola pasan a file/sync por
 * lo mismo. Para replicar producción exactamente, editar el .env generado y
 * poner DB_CONNECTION=mysql con sus credenciales.
 */
const AJUSTES = [
    'APP_NAME' => '"Portafolio de Reportes BI"',
    'APP_ENV' => 'local',
    'APP_DEBUG' => 'true',
    'APP_URL' => 'http://localhost:8010',
    'APP_LOCALE' => 'es',
    'APP_FALLBACK_LOCALE' => 'es',
    'APP_FAKER_LOCALE' => 'es_ES',
    'DB_CONNECTION' => 'sqlite',
    'SESSION_DRIVER' => 'file',
    'CACHE_STORE' => 'file',
    'QUEUE_CONNECTION' => 'sync',
];

/**
 * Claves de MySQL que sqlite no necesita: se comentan en lugar de borrarse,
 * para que queden a la vista si alguien quiere volver a MySQL.
 *
 * @var array<int, string>
 */
const A_COMENTAR = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];

$raiz = __DIR__;
$destino = $raiz.'/.env';
$plantilla = $raiz.'/.env.example';

if (file_exists($destino)) {
    echo "El archivo .env ya existe, se deja como está.\n";
    exit(0);
}

if (! file_exists($plantilla)) {
    fwrite(STDERR, "No encuentro .env.example junto a este script.\n");
    exit(1);
}

$lineas = file($plantilla, FILE_IGNORE_NEW_LINES);
$salida = [];

foreach ($lineas as $linea) {
    if (! preg_match('/^([A-Z0-9_]+)=/', $linea, $coincidencia)) {
        $salida[] = $linea;

        continue;
    }

    $clave = $coincidencia[1];

    if (isset(AJUSTES[$clave])) {
        $salida[] = $clave.'='.AJUSTES[$clave];

        continue;
    }

    if (in_array($clave, A_COMENTAR, true)) {
        $salida[] = '# '.$linea;

        continue;
    }

    $salida[] = $linea;
}

file_put_contents($destino, implode("\n", $salida)."\n");

echo "Archivo .env creado para el entorno local (base sqlite).\n";
