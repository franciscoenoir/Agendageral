<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DemandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pasta_id'    => 'nullable|exists:pastas,id',
            'titulo'      => 'required|string|max:255',
            'categoria'   => 'required|string|max:100',
            'urgencia'    => 'required|in:urgente,alta,media,baixa',
            'data_inicio' => 'nullable|date',
            'data_limite' => 'required|date',
            'responsavel' => 'nullable|string|max:255',
            'valor'           => 'nullable|numeric|min:0',
            'observacoes'     => 'nullable|string',
            'recorrente'      => 'nullable|boolean',
            'frequencia'      => 'nullable|in:semanal,quinzenal,mensal,anual',
            'checklist'       => 'nullable|array',
            'checklist.*'     => 'required_with:checklist|string|max:255',
            'links'       => 'nullable|array',
            'links.*.url' => 'required_with:links|url',
            'links.*.label' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required'   => 'O título é obrigatório.',
            'categoria.required' => 'Selecione uma categoria.',
            'urgencia.required' => 'Selecione a urgência.',
            'data_limite.required' => 'A data limite é obrigatória.',
            'links.*.url.url'   => 'Um dos links não é uma URL válida.',
        ];
    }
}
