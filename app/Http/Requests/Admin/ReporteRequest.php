<?php

namespace App\Http\Requests\Admin;

use App\Enums\TipoReporte;
use App\Models\Reporte;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'titulo' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'tipo' => ['required', Rule::enum(TipoReporte::class)],
            // Se acepta la URL o el <iframe> completo que entrega Power BI
            'url_iframe' => ['required', 'string', 'max:4000'],
            'activo' => ['nullable', 'boolean'],
            'publico' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'area_id' => 'área',
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'tipo' => 'tipo',
            'url_iframe' => 'URL del iframe',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'url_iframe' => Reporte::extraerUrl((string) $this->input('url_iframe')),
        ]);
    }

    /**
     * Valida que el src sea una URL https, salvo el marcador «demo».
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $url = (string) $this->input('url_iframe');

            if ($url === Reporte::DEMO) {
                return;
            }

            if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_starts_with($url, 'https://')) {
                $v->errors()->add('url_iframe', 'Pega la URL de inserción (https://…) o el código <iframe> que entrega Power BI.');
            }
        });
    }

    /**
     * El orden no se teclea: el reporte nuevo se va al final de su área y
     * desde ahí se mueve con las flechas del listado.
     *
     * @return array{area_id:int,titulo:string,descripcion:?string,tipo:string,url_iframe:string,orden:int,activo:bool,publico:bool}
     */
    public function datos(?Reporte $reporte = null): array
    {
        $v = $this->validated();

        return [
            'area_id' => (int) $v['area_id'],
            'titulo' => $v['titulo'],
            'descripcion' => $v['descripcion'] ?? null,
            'tipo' => $v['tipo'],
            'url_iframe' => $v['url_iframe'],
            'orden' => $reporte?->orden ?? ((int) Reporte::query()->where('area_id', (int) $v['area_id'])->max('orden') + 1),
            'activo' => $this->boolean('activo'),
            'publico' => $this->boolean('publico'),
        ];
    }
}
