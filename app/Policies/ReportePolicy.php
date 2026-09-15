<?php

namespace App\Policies;

use App\Models\Reporte;
use App\Models\User;

class ReportePolicy
{
    /** Ver el reporte (su iframe). */
    public function view(User $usuario, Reporte $reporte): bool
    {
        return $reporte->puedeVerlo($usuario);
    }

    public function create(User $usuario): bool
    {
        return $usuario->esAdministrador();
    }

    public function update(User $usuario, Reporte $reporte): bool
    {
        return $usuario->esAdministrador();
    }

    public function delete(User $usuario, Reporte $reporte): bool
    {
        return $usuario->esAdministrador();
    }
}
