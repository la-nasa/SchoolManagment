<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('create-evaluations');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:evaluations',
            'exam_date' => 'required|date|after_or_equal:today',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'term_id' => 'required|exists:terms,id',
            'school_year_id' => 'required|exists:school_years,id',
            'max_marks' => 'required|numeric|min:1|max:100',
            'pass_marks' => 'required|numeric|min:0|max:100|lte:max_marks',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.unique' => 'Ce titre existe déjà.',
            'exam_date.after_or_equal' => 'La date doit être égale ou après aujourd\'hui.',
            'class_id.required' => 'Veuillez sélectionner une classe.',
            'subject_id.required' => 'Veuillez sélectionner une matière.',
            'pass_marks.lte' => 'La note de passage ne peut pas dépasser la note maximum.',
        ];
    }
}
