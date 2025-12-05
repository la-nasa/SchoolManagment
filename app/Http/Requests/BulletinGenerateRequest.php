<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulletinGenerateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('generate-reports');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'term_id' => 'required|exists:terms,id',
            'school_year_id' => 'required|exists:school_years,id',
            'type' => 'required|in:standard,apc',
        ];
    }

    public function messages(): array
    {
        return [
            'term_id.required' => 'Veuillez sélectionner un trimestre.',
            'school_year_id.required' => 'Veuillez sélectionner une année scolaire.',
            'type.in' => 'Le type de bulletin doit être standard ou APC.',
        ];
    }
}
