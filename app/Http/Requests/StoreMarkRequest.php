<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('create-marks');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marks' => [
                'array',
                'required',
                function ($attribute, $value, $fail) {
                    if (empty($value) || count($value) === 0) {
                        $fail('Au moins une note doit être saisie.');
                    }
                }
            ],
            'marks.*' => 'nullable|numeric|min:0|max:100',
            'is_absent' => 'nullable|array',
            'is_absent.*' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'marks.required' => 'Les notes sont obligatoires.',
            'marks.*.numeric' => 'La note doit être un nombre.',
            'marks.*.max' => 'La note ne peut pas dépasser 100.',
        ];
    }
}
