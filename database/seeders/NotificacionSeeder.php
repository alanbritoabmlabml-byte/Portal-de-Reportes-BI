<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Enums\TipoNotificacion;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();

        Notificacion::avisar(
            $usuarios,
            'Bienvenido al Portafolio de Reportes BI',
            'Desde aquí accedes al portafolio, a los tableros de Power BI de tu área y a los sitios de la empresa. Ancla tus favoritos para tenerlos siempre arriba.',
            TipoNotificacion::Exito,
        );

        Notificacion::avisar(
            $usuarios->where('rol', Rol::Administrador),
            'Reemplaza los tableros de muestra',
            'Los reportes nacen con un tablero de ejemplo. En Administración → Reportes pega la URL de inserción de cada informe de Power BI y define quién puede verlo.',
            TipoNotificacion::Alerta,
            route('admin.reportes.index'),
        );
    }
}
