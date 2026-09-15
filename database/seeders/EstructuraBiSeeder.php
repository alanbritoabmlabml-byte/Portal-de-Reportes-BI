<?php

namespace Database\Seeders;

use App\Enums\TipoReporte;
use App\Models\Area;
use App\Models\Departamento;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Departamentos → áreas → reportes de ejemplo.
 *
 * Los reportes nacen con url_iframe = «demo»: el visor muestra un tablero de
 * muestra propio en lugar de Power BI. Desde el panel de administración se
 * reemplaza por la URL de inserción real de cada informe.
 */
class EstructuraBiSeeder extends Seeder
{
    public function run(): void
    {
        $estructura = [
            [
                'nombre' => 'Administración', 'icono' => 'edificio', 'descripcion' => 'Dirección, gerencia y áreas de apoyo',
                'areas' => [
                    ['nombre' => 'Dirección', 'icono' => 'brujula', 'descripcion' => 'Tableros para el Directorio', 'reportes' => [
                        ['Resultados corporativos', TipoReporte::Direccion, 'Estado de resultados, flujo y márgenes consolidados', false],
                        ['Indicadores estratégicos', TipoReporte::Direccion, 'Seguimiento de los pilares de la transformación', false],
                        ['Control de inversiones', TipoReporte::Control, 'Avance de CAPEX y proyectos aprobados', false],
                    ]],
                    ['nombre' => 'Gerencia', 'icono' => 'maletin', 'descripcion' => 'Tableros gerenciales por unidad', 'reportes' => [
                        ['Tablero gerencial semanal', TipoReporte::Gerencial, 'Ventas, producción y cobranzas de la semana', true],
                        ['Planificación anual', TipoReporte::Planificacion, 'Presupuesto vs. ejecutado por unidad', false],
                    ]],
                    ['nombre' => 'RRHH', 'icono' => 'personas', 'descripcion' => 'Personal, asistencia y clima', 'reportes' => [
                        ['Control de asistencia', TipoReporte::Asistencia, 'Marcaciones, atrasos, faltas y horas extra por departamento', true],
                        ['Dotación de personal', TipoReporte::Gerencial, 'Personal por departamento, altas y bajas', false],
                        ['Vacaciones y licencias', TipoReporte::Control, 'Saldos de vacaciones y licencias vigentes', false],
                        ['Planificación de turnos', TipoReporte::Planificacion, 'Cobertura de turnos por área de planta', false],
                    ]],
                    ['nombre' => 'Contabilidad y Finanzas', 'icono' => 'moneda', 'descripcion' => 'Cuentas, cobranzas y tesorería', 'reportes' => [
                        ['Cobranzas y cartera', TipoReporte::Gerencial, 'Antigüedad de saldos y cobranza del mes', false],
                        ['Flujo de caja', TipoReporte::Direccion, 'Proyección de caja a 30/60/90 días', false],
                    ]],
                ],
            ],
            [
                'nombre' => 'Producción', 'icono' => 'fabrica', 'descripcion' => 'Plantas y líneas de producción',
                'areas' => [
                    ['nombre' => 'Bolsas', 'icono' => 'bolsa', 'descripcion' => 'Extrusión, impresión y sellado de bolsas', 'reportes' => [
                        ['Producción diaria de bolsas', TipoReporte::Operativo, 'Kilos producidos, merma y paradas por máquina', true],
                        ['Supervisión de turnos', TipoReporte::Supervision, 'Cumplimiento de programa por turno y supervisor', false],
                        ['Control de calidad', TipoReporte::Control, 'Rechazos, reprocesos y no conformidades', false],
                    ]],
                    ['nombre' => 'Termoformado', 'icono' => 'vaso', 'descripcion' => 'Vasos, envases y descartables', 'reportes' => [
                        ['Producción de termoformado', TipoReporte::Operativo, 'Unidades por línea, eficiencia y scrap', true],
                        ['Planificación de producción', TipoReporte::Planificacion, 'Programa semanal vs. pedidos pendientes', false],
                    ]],
                    ['nombre' => 'Expandido', 'icono' => 'cubo', 'descripcion' => 'Poliestireno expandido', 'reportes' => [
                        ['Producción de expandido', TipoReporte::Operativo, 'Bloques y piezas por turno, densidad y merma', true],
                        ['Consumo de materia prima', TipoReporte::Control, 'Consumo de perlas vs. estándar', false],
                    ]],
                    ['nombre' => 'Inyección', 'icono' => 'engranaje', 'descripcion' => 'Piezas inyectadas', 'reportes' => [
                        ['Producción de inyección', TipoReporte::Operativo, 'Ciclos, piezas buenas y tiempos de cambio de molde', true],
                        ['Supervisión de máquinas', TipoReporte::Supervision, 'Disponibilidad y OEE por inyectora', false],
                    ]],
                    ['nombre' => 'Extrusión', 'icono' => 'rollo', 'descripcion' => 'Líneas de extrusión de film', 'reportes' => [
                        ['Inventario de extrusión (SIMEC)', TipoReporte::Control, 'Rollos en proceso y stock por línea', false],
                        ['Rendimiento de extrusoras', TipoReporte::Operativo, 'Kg/hora, paradas y consumo energético', true],
                    ]],
                ],
            ],
            [
                'nombre' => 'Comercial', 'icono' => 'tienda', 'descripcion' => 'Ventas, marketing y distribuidores',
                'areas' => [
                    ['nombre' => 'Ventas', 'icono' => 'grafico', 'descripcion' => 'Ventas por canal, cliente y producto', 'reportes' => [
                        ['Ventas del mes', TipoReporte::Gerencial, 'Facturación por línea, vendedor y región', true],
                        ['Seguimiento de pedidos', TipoReporte::Operativo, 'Pedidos abiertos, despachados y atrasados', false],
                    ]],
                    ['nombre' => 'Marketing', 'icono' => 'megafono', 'descripcion' => 'Campañas y presencia digital', 'reportes' => [
                        ['Indicadores digitales', TipoReporte::Gerencial, 'Tráfico web, redes y campañas activas', false],
                    ]],
                    ['nombre' => 'Distribuidores', 'icono' => 'camion', 'descripcion' => 'Red de distribución', 'reportes' => [
                        ['Desempeño de distribuidores', TipoReporte::Supervision, 'Compras, cobertura y cumplimiento de metas', false],
                    ]],
                ],
            ],
            [
                'nombre' => 'Logística', 'icono' => 'almacen', 'descripcion' => 'Almacenes y abastecimiento',
                'areas' => [
                    ['nombre' => 'Almacenes', 'icono' => 'cajas', 'descripcion' => 'Stock de producto terminado', 'reportes' => [
                        ['Inventario de producto terminado', TipoReporte::Control, 'Stock por almacén, rotación y quiebres', true],
                    ]],
                    ['nombre' => 'Materia Prima', 'icono' => 'pellets', 'descripcion' => 'Abastecimiento de resinas e insumos', 'reportes' => [
                        ['Stock y compras de materia prima', TipoReporte::Control, 'Saldos, mezclas y órdenes de compra en curso', false],
                        ['Planificación de abastecimiento', TipoReporte::Planificacion, 'Cobertura en días por resina', false],
                    ]],
                ],
            ],
            [
                'nombre' => 'Sistemas', 'icono' => 'chip', 'descripcion' => 'TI, infraestructura y calidad web',
                'areas' => [
                    ['nombre' => 'Tecnología de la Información', 'icono' => 'chip', 'descripcion' => 'Infraestructura, soporte y SQA web', 'reportes' => [
                        ['Calidad de sitios web (SQA)', TipoReporte::Control, 'Disponibilidad, errores y seguridad de los sitios', true],
                        ['Actividades semanales de TI', TipoReporte::Gerencial, 'Avance del equipo y puntos críticos de la semana', false],
                    ]],
                ],
            ],
        ];

        $ordenDepartamento = 0;

        foreach ($estructura as $d) {
            $departamento = Departamento::query()->updateOrCreate(
                ['slug' => Str::slug($d['nombre'])],
                ['nombre' => $d['nombre'], 'icono' => $d['icono'], 'descripcion' => $d['descripcion'], 'orden' => $ordenDepartamento++],
            );

            $ordenArea = 0;

            foreach ($d['areas'] as $a) {
                $area = Area::query()->updateOrCreate(
                    ['slug' => Str::slug($a['nombre'])],
                    [
                        'departamento_id' => $departamento->id,
                        'nombre' => $a['nombre'],
                        'icono' => $a['icono'],
                        'descripcion' => $a['descripcion'],
                        'orden' => $ordenArea++,
                    ],
                );

                $ordenReporte = 0;

                foreach ($a['reportes'] as [$titulo, $tipo, $descripcion, $publico]) {
                    Reporte::query()->updateOrCreate(
                        ['area_id' => $area->id, 'titulo' => $titulo],
                        [
                            'tipo' => $tipo,
                            'descripcion' => $descripcion,
                            'url_iframe' => 'demo',
                            'orden' => $ordenReporte++,
                            'activo' => true,
                            'publico' => $publico,
                        ],
                    );
                }
            }
        }

        $this->asignarAccesosDeEjemplo();
    }

    /** Accesos de vista de muestra para que se vea la diferencia entre usuarios. */
    private function asignarAccesosDeEjemplo(): void
    {
        $rrhh = User::query()->where('email', 'rrhh.prueba@plasticoscarmen.com')->first();
        $planta = User::query()->where('email', 'planta.prueba@plasticoscarmen.com')->first();
        $edwin = User::query()->where('email', 'emoscoso@plasticoscarmen.com')->first();

        if ($rrhh) {
            $ids = Reporte::query()->whereHas('area', fn ($q) => $q->where('slug', 'rrhh'))->pluck('id');
            $rrhh->reportes()->syncWithoutDetaching($ids->all());
        }

        if ($planta) {
            $ids = Reporte::query()
                ->whereHas('area.departamento', fn ($q) => $q->where('slug', 'produccion'))
                ->where('tipo', TipoReporte::Supervision)
                ->pluck('id');
            $planta->reportes()->syncWithoutDetaching($ids->all());
        }

        if ($edwin) {
            $ids = Reporte::query()->whereIn('tipo', [TipoReporte::Direccion, TipoReporte::Gerencial])->pluck('id');
            $edwin->reportes()->syncWithoutDetaching($ids->all());
        }
    }
}
