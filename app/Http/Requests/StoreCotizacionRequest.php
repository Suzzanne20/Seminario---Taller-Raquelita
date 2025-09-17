<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'descripcion' => ['required','string','max:200'],
            'type_service_id' => ['required','exists:type_service,id'],
            'costo_mo' => ['nullable','numeric','min:0'],
            'insumos' => ['required','array','min:1'],
            'insumos.*.id' => ['required','exists:insumo,id'],
            'insumos.*.cantidad' => ['required','numeric','min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'insumos.required' => 'Agrega al menos un insumo con cantidad.',
        ];
    }
}
