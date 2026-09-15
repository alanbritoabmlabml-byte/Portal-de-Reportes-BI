<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccionBitacora;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Rastro;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function mostrar(): View
    {
        return view('auth.login');
    }

    public function entrar(LoginRequest $request): RedirectResponse
    {
        $request->autenticar();

        $request->session()->regenerate();

        Rastro::olvidar();
        Rastro::sesion($request);
        Rastro::registrar(AccionBitacora::Entrar, 'Inició sesión en el portal');

        return redirect()->intended(route('menu'));
    }

    public function salir(Request $request): RedirectResponse
    {
        Rastro::registrar(AccionBitacora::Salir, 'Cerró sesión');
        Rastro::cerrar('salida', $request);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
