@extends('layouts.app')

@section('title', 'Créer une Évaluation')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Créer une Nouvelle Évaluation</h3>
            <p class="mt-1 text-sm text-gray-500">Remplissez les informations pour créer une nouvelle évaluation</p>
        </div>

        <form action="{{ route('admin.evaluations.store') }}" method="POST" class="px-4 py-5 sm:p-6">
            @csrf

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Titre de l'évaluation *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="mt-1 form-input @error('title') border-red-500 @enderror"
                               placeholder="Ex: Contrôle de Mathématiques">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700">Matière *</label>
                        <select name="subject_id" id="subject_id" required
                                class="mt-1 form-select @error('subject_id') border-red-500 @enderror">
                            <option value="">Sélectionnez une matière</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="class_id" class="block text-sm font-medium text-gray-700">Classe *</label>
                        <select name="class_id" id="class_id" required
                                class="mt-1 form-select @error('class_id') border-red-500 @enderror">
                            <option value="">Sélectionnez une classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="teacher_id" class="block text-sm font-medium text-gray-700">Enseignant *</label>
                        <select name="teacher_id" id="teacher_id" required
                                class="mt-1 form-select @error('teacher_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un enseignant</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }} ({{ $teacher->matricule }})
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Evaluation Details -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label for="exam_type_id" class="block text-sm font-medium text-gray-700">Type d'évaluation *</label>
                        <select name="exam_type_id" id="exam_type_id" required
                                class="mt-1 form-select @error('exam_type_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un Type</option>
                            @foreach ($examTypes as $type)
                                <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('exam_type_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="term_id" class="block text-sm font-medium text-gray-700">Trimestre *</label>
                        <select name="term_id" id="term_id" required
                                class="mt-1 form-select @error('term_id') border-red-500 @enderror">
                            <option value="">Sélectionnez un trimestre</option>
                            @foreach ($terms as $term)
                                <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('term_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="school_year_id" class="block text-sm font-medium text-gray-700">Année scolaire *</label>
                        <select name="school_year_id" id="school_year_id" required
                                class="mt-1 form-select @error('school_year_id') border-red-500 @enderror">
                            <option value="">Sélectionnez une année</option>
                            @foreach($schoolYears as $year)
                                <option value="{{ $year->id }}" {{ old('school_year_id', $currentSchoolYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_year_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Dates and Marks -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div>
                        <label for="exam_date" class="block text-sm font-medium text-gray-700">Date de l'évaluation *</label>
                        <input type="date" name="exam_date" id="exam_date" value="{{ old('exam_date', date('Y-m-d')) }}" required
                               class="mt-1 form-input @error('exam_date') border-red-500 @enderror">
                        @error('exam_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_marks" class="block text-sm font-medium text-gray-700">Note maximale *</label>
                        <input type="number" name="max_marks" id="max_marks" value="{{ old('max_marks', 20) }}" required
                               class="mt-1 form-input @error('max_marks') border-red-500 @enderror"
                               min="10" max="100" step="1">
                        @error('max_marks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pass_marks" class="block text-sm font-medium text-gray-700">Note de passage *</label>
                        <input type="number" name="pass_marks" id="pass_marks" value="{{ old('pass_marks', 10) }}" required
                               class="mt-1 form-input @error('pass_marks') border-red-500 @enderror"
                               min="0" max="100" step="0.5">
                        @error('pass_marks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Note minimale pour réussir</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 form-textarea @error('description') border-red-500 @enderror"
                              placeholder="Description de l'évaluation, consignes, etc...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Instructions -->
                <div>
                    <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions</label>
                    <textarea name="instructions" id="instructions" rows="3"
                              class="mt-1 form-textarea @error('instructions') border-red-500 @enderror"
                              placeholder="Instructions particulières...">{{ old('instructions') }}</textarea>
                    @error('instructions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                    <div class="mt-2 space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="status_planned" name="status" value="planned"
                                   {{ old('status', 'planned') == 'planned' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="status_planned" class="ml-3 block text-sm font-medium text-gray-700">Planifiée</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_in_progress" name="status" value="in_progress"
                                   {{ old('status') == 'in_progress' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="status_in_progress" class="ml-3 block text-sm font-medium text-gray-700">En cours</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="status_completed" name="status" value="completed"
                                   {{ old('status') == 'completed' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="status_completed" class="ml-3 block text-sm font-medium text-gray-700">Terminée</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.evaluations.index') }}" class="btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn-primary">
                    Créer l'Évaluation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-input {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-select {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-textarea {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
}

.btn-secondary {
    @apply inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default school year if not set
    const schoolYearSelect = document.getElementById('school_year_id');
    if (schoolYearSelect && !schoolYearSelect.value) {
        const currentYearOption = schoolYearSelect.querySelector('option[selected]');
        if (currentYearOption) {
            schoolYearSelect.value = currentYearOption.value;
        }
    }

    // Validation pour les notes
    const maxMarksInput = document.getElementById('max_marks');
    const passMarksInput = document.getElementById('pass_marks');

    function validateMarks() {
        const maxMarks = parseFloat(maxMarksInput.value);
        const passMarks = parseFloat(passMarksInput.value);

        if (passMarks > maxMarks) {
            passMarksInput.setCustomValidity('La note de passage ne peut pas être supérieure à la note maximale');
        } else {
            passMarksInput.setCustomValidity('');
        }
    }

    maxMarksInput.addEventListener('change', validateMarks);
    passMarksInput.addEventListener('change', validateMarks);
});
</script>
@endpush
