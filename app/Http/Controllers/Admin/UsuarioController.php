<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccionBitacora;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Usuarios de la aplicación. Cuando llegue la base definitiva se importará
 * sobre esta misma tabla; el panel queda para altas y ajustes puntuales.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $consulta = User::query()->withCount('reportes')->orderBy('name');

        if ($busqueda = trim((string) $request->query('q'))) {
            $consulta->where(fn ($q) => $q->where('name', 'like', "%{$busqueda}%")->orWhere('email', 'like', "%{$busqueda}%"));
        }

        if ($rol = Rol::tryFrom((string) $request->query('rol'))) {
            $consulta->where('rol', $rol);
        }

        return view('admin.usuarios.index', [
            'usuarios' => $consulta->paginate(15)->withQueryString(),
            'roles' => Rol::opciones(),
            'filtros' => ['q' => $busqueda, 'rol' => $rol?->value],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'rol' => ['required', Rule::enum(Rol::class)],
            'activo' => ['nullable', 'boolean'],
        ], [], ['name' => 'nombre', 'email' => 'correo', 'password' => 'contraseña']);

        $usuario = User::query()->create($datos + ['activo' => $request->boolean('activo', true)]);

        Rastro::registrar(AccionBitacora::Crear, "Creó el usuario «{$usuario->name}» ({$usuario->email})", 'Usuario', $usuario->getKey());

        return redirect()->route('admin.usuarios.index')->with('aviso', "Usuario «{$usuario->name}» creado.");
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($usuario)],
            'password' => ['nullable', 'string', Password::min(8)],
            'rol' => ['required', Rule::enum(Rol::class)],
            'activo' => ['nullable', 'boolean'],
        ], [], ['name' => 'nombre', 'email' => 'correo', 'password' => 'contraseña']);

        // El administrador no puede quitarse a sí mismo el rol ni desactivarse
        if ($usuario->is($request->user())) {
            $datos['rol'] = Rol::Administrador->value;
            $datos['activo'] = true;
        } else {
            $datos['activo'] = $request->boolean('activo');
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        $antes = $usuario->only(['name', 'email', 'rol', 'activo']);
        $usuario->update($datos);

        Rastro::registrar(
            AccionBitacora::Editar,
            "Editó al usuario «{$usuario->name}»",
            'Usuario',
            $usuario->getKey(),
            ['antes' => $antes, 'despues' => $usuario->only(['name', 'email', 'rol', 'activo'])],
        );

        return back()->with('aviso', "Usuario «{$usuario->name}» actualizado.");
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        abort_if($usuario->is($request->user()), 403, 'No puedes eliminar tu propio usuario.');

        $nombre = $usuario->name;
        $usuario->delete();

        Rastro::registrar(AccionBitacora::Eliminar, "Eliminó al usuario «{$nombre}»", 'Usuario', $usuario->getKey());

        return back()->with('aviso', "Usuario «{$nombre}» eliminado.");
    }
}
