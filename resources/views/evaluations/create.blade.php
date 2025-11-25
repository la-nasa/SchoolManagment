@extends('layouts.app')

@section('title', 'Créer une Évaluation')
@section('page-title', 'Créer une Évaluation')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.evaluations.index') }}">Évaluations</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Créer une Nouvelle Évaluation</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.evaluations.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Titre de l'évaluation *</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Ex: Contrôle de Mathématiques">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subject_id" class="form-label">Matière *</label>
                            <select name="subject_id" id="subject_id" required
                                class="form-select @error('subject_id') is-invalid @enderror">
                                <option value="">Sélectionnez une matière</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Classe *</label>
                            <select name="class_id" id="class_id" required
                                class="form-select @error('class_id') is-invalid @enderror">
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->full_name ?? $class->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="teacher_id" class="form-label">Enseignant *</label>
                            <select name="teacher_id" id="teacher_id" required
                                class="form-select @error('teacher_id') is-invalid @enderror">
                                <option value="">Sélectionnez un enseignant</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->matricule }})
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="exam_type_id" class="form-label">Type d'évaluation *</label>
                            <select name="exam_type_id" id="exam_type_id" required
                                class="form-select @error('exam_type_id') is-invalid @enderror">
                                <option value="">Sélectionnez un type</option>
                                @foreach ($examTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="term_id" class="form-label">Trimestre *</label>
                            <select name="term_id" id="term_id" required
                                class="form-select @error('term_id') is-invalid @enderror">
                                <option value="">Sélectionnez un trimestre</option>
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id') == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="school_year_id" class="form-label">Année scolaire *</label>
                            <select name="school_year_id" id="school_year_id" required
                                class="form-select @error('school_year_id') is-invalid @enderror">
                                <option value="">Sélectionnez une année</option>
                                @foreach($schoolYears as $year)
                                    <option value="{{ $year->id }}" {{ old('school_year_id', $currentSchoolYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="exam_date" class="form-label">Date de l'évaluation *</label>
                            <input type="date" name="exam_date" id="exam_date" value="{{ old('exam_date', date('Y-m-d')) }}" required
                                class="form-control @error('exam_date') is-invalid @enderror">
                            @error('exam_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="max_marks" class="form-label">Note maximale *</label>
                            <input type="number" name="max_marks" id="max_marks" value="{{ old('max_marks', 20) }}" required
                                class="form-control @error('max_marks') is-invalid @enderror"
                                min="10" max="100" step="1">
                            @error('max_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="pass_marks" class="form-label">Note de passage *</label>
                            <input type="number" name="pass_marks" id="pass_marks" value="{{ old('pass_marks', 10) }}" required
                                class="form-control @error('pass_marks') is-invalid @enderror"
                                min="0" max="100" step="0.5">
                            @error('pass_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Note minimale pour réussir</div>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Description de l'évaluation, consignes, etc...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea name="instructions" id="instructions" rows="3"
                                class="form-control @error('instructions') is-invalid @enderror"
                                placeholder="Instructions particulières...">{{ old('instructions') }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Statut</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_planned" value="planned"
                                    {{ old('status', 'planned') == 'planned' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_planned">Planifiée</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_in_progress" value="in_progress"
                                    {{ old('status') == 'in_progress' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_in_progress">En cours</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed"
                                    {{ old('status') == 'completed' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_completed">Terminée</label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Créer l'Évaluation
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb me-2"></i>À savoir</h6>
                    <ul class="mb-0 ps-3">
                        <li>Les évaluations permettent de suivre le progrès des élèves</li>
                        <li>Les notes doivent être saisies après la création</li>
                        <li>Le taux de complétion indique le pourcentage de notes saisies</li>
                        <li>Les champs marqués d'un * sont obligatoires</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6>Types d'évaluation:</h6>
                    <ul class="list-unstyled small">
                        @foreach($examTypes as $type)
                        <li><span class="badge bg-info">{{ $type->name }}</span> - {{ $type->description ?? 'Évaluation standard' }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation pour les notes
    const maxMarksInput = document.getElementById('max_marks');
    const passMarksInput = document.getElementById('pass_marks');

    function validateMarks() {
        const maxMarks = parseFloat(maxMarksInput.value);
        const passMarks = parseFloat(passMarksInput.value);

        if (passMarks > maxMarks) {
            passMarksInput.setCustomValidity('La note de passage ne peut pas être supérieure à la note maximale');
            passMarksInput.classList.add('is-invalid');
        } else {
            passMarksInput.setCustomValidity('');
            passMarksInput.classList.remove('is-invalid');
        }
    }

    maxMarksInput.addEventListener('change', validateMarks);
    passMarksInput.addEventListener('change', validateMarks);

    // Initial validation
    validateMarks();

    // Initialize Select2
    $('#subject_id, #class_id, #teacher_id, #exam_type_id, #term_id, #school_year_id').select2({
        placeholder: 'Sélectionnez une option',
        allowClear: true
    });
});
</script>
@endpush
