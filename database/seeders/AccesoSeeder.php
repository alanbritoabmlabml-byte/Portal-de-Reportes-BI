<?php

namespace Database\Seeders;

use App\Enums\GrupoAcceso;
use App\Models\Acceso;
use Illuminate\Database\Seeder;

/**
 * Tarjetas del menú principal. Las URL internas (asistencia, SharePoint) se
 * ajustan desde Administración → Tarjetas si cambian.
 */
class AccesoSeeder extends Seeder
{
    public function run(): void
    {
        $tarjetas = [
            [
                'nombre' => 'Portafolio de Transformación',
                'grupo' => GrupoAcceso::Propio,
                'descripcion' => 'Iniciativas, actividades y cronograma de la transformación corporativa.',
                'url' => 'https://portafolio.plasticoscarmen.com',
                'texto_boton' => 'Ingresar',
                'imagen' => 'img/tarjetas/portafolio.svg',
                'tono' => 'azul',
                'etiqueta' => 'Interno',
                'destacado' => true,
            ],
            [
                'nombre' => 'Control de Asistencia',
                'grupo' => GrupoAcceso::Propio,
                'descripcion' => 'Marcaciones, atrasos, faltas y horas extra del personal.',
                'url' => 'http://192.0.0.8:8080/asistencia/',
                'texto_boton' => 'Ingresar',
                'imagen' => 'img/tarjetas/asistencia.svg',
                'tono' => 'tinta',
                'etiqueta' => 'Red interna',
            ],
            [
                'nombre' => 'SharePoint Dirección',
                'grupo' => GrupoAcceso::SharePoint,
                'descripcion' => 'Documentos ejecutivos, tableros y gobierno del Directorio.',
                'url' => 'https://plasticoscarmenbo.sharepoint.com/sites/Direccion',
                'texto_boton' => 'Abrir sitio',
                'imagen' => 'img/tarjetas/sharepoint-direccion.svg',
                'tono' => 'azul',
                'etiqueta' => 'Microsoft 365',
            ],
            [
                'nombre' => 'SharePoint Sistemas',
                'grupo' => GrupoAcceso::SharePoint,
                'descripcion' => 'Informes semanales, planillas de actividades y SQA web del área de TI.',
                'url' => 'https://plasticoscarmenbo.sharepoint.com/sites/SISTEMASPC',
                'texto_boton' => 'Abrir sitio',
                'imagen' => 'img/tarjetas/sharepoint-sistemas.svg',
                'tono' => 'azul',
                'etiqueta' => 'Microsoft 365',
            ],
            [
                'nombre' => 'Sitio web Plásticos Carmen',
                'grupo' => GrupoAcceso::Publico,
                'descripcion' => 'Página institucional, catálogo de productos y folletos.',
                'url' => 'https://plasticoscarmen.com',
                'texto_boton' => 'Visitar sitio',
                'imagen' => 'img/tarjetas/web-pc.svg',
                'tono' => 'rojo',
                'etiqueta' => 'Público',
            ],
            [
                'nombre' => 'Portal de Distribuidores',
                'grupo' => GrupoAcceso::Publico,
                'descripcion' => 'Red de distribuidores autorizados y puntos de venta.',
                'url' => 'https://plasticoscarmen.com/distribuidores/',
                'texto_boton' => 'Visitar sitio',
                'imagen' => 'img/tarjetas/distribuidores.svg',
                'tono' => 'rojo',
                'etiqueta' => 'Público',
            ],
            [
                'nombre' => 'Materia Prima S.R.L.',
                'grupo' => GrupoAcceso::Publico,
                'descripcion' => 'Resinas, masterbatch y fichas técnicas de producto.',
                'url' => 'https://materiaprima.com.bo',
                'texto_boton' => 'Visitar sitio',
                'imagen' => 'img/tarjetas/materia-prima.svg',
                'tono' => 'claro',
                'etiqueta' => 'Público',
            ],
            [
                'nombre' => 'Correo y Teams',
                'grupo' => GrupoAcceso::Microsoft,
                'descripcion' => 'Outlook, Teams y el resto de aplicaciones de Microsoft 365.',
                'url' => 'https://www.office.com',
                'texto_boton' => 'Abrir Microsoft 365',
                'imagen' => 'img/tarjetas/microsoft-365.svg',
                'tono' => 'claro',
                'etiqueta' => 'Microsoft 365',
            ],
            [
                'nombre' => 'Servicio Power BI',
                'grupo' => GrupoAcceso::Microsoft,
                'descripcion' => 'Espacios de trabajo y publicación de informes en app.powerbi.com.',
                'url' => 'https://app.powerbi.com',
                'texto_boton' => 'Abrir Power BI',
                'imagen' => 'img/tarjetas/power-bi.svg',
                'tono' => 'tinta',
                'etiqueta' => 'Microsoft 365',
            ],
        ];

        $contadores = [];

        foreach ($tarjetas as $datos) {
            $grupo = $datos['grupo']->value;
            $orden = $contadores[$grupo] = ($contadores[$grupo] ?? -1) + 1;

            Acceso::query()->updateOrCreate(
                ['nombre' => $datos['nombre']],
                $datos + ['orden' => $orden, 'activo' => true, 'nueva_pestana' => true, 'destacado' => false],
            );
        }
    }
}
