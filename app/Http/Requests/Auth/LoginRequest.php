<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Intenta la sesión. Una cuenta desactivada falla igual que una clave
     * incorrecta: no se le dice al visitante cuál de las dos fue.
     *
     * @throws ValidationException
     */
    public function autenticar(): void
    {
        $credenciales = $this->only('email', 'password') + ['activo' => true];

        if (! Auth::attempt($credenciales, $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con ningún usuario activo.',
            ]);
        }
    }
}
