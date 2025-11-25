@extends('layouts.app')

@section('title', 'Modifier l\'Évaluation - ' . $evaluation->title)
@section('page-title', 'Modifier l\'Évaluation')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.evaluations.index') }}">Évaluations</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.evaluations.show', $evaluation) }}">{{ $evaluation->title }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Modifier l'évaluation</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.evaluations.update', $evaluation) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Titre de l'évaluation *</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $evaluation->title) }}" required
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
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $evaluation->subject_id) == $subject->id ? 'selected' : '' }}>
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
                                    <option value="{{ $class->id }}" {{ old('class_id', $evaluation->class_id) == $class->id ? 'selected' : '' }}>
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
                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $evaluation->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->matricule ?? 'N/A' }})
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
                                @foreach($examTypes as $examType)
                                    <option value="{{ $examType->id }}" {{ old('exam_type_id', $evaluation->exam_type_id) == $examType->id ? 'selected' : '' }}>
                                        {{ $examType->name }}
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
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ old('term_id', $evaluation->term_id) == $term->id ? 'selected' : '' }}>
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
                                @foreach($schoolYears as $schoolYear)
                                    <option value="{{ $schoolYear->id }}" {{ old('school_year_id', $evaluation->school_year_id) == $schoolYear->id ? 'selected' : '' }}>
                                        {{ $schoolYear->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="exam_date" class="form-label">Date de l'évaluation *</label>
                            <input type="date" name="exam_date" id="exam_date"
                                value="{{ old('exam_date', $evaluation->exam_date ? $evaluation->exam_date->format('Y-m-d') : ($evaluation->evaluation_date ? $evaluation->evaluation_date->format('Y-m-d') : '')) }}"
                                required
                                class="form-control @error('exam_date') is-invalid @enderror">
                            @error('exam_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="max_marks" class="form-label">Note maximale *</label>
                            <input type="number" name="max_marks" id="max_marks"
                                value="{{ old('max_marks', $evaluation->max_marks ?? $evaluation->max_mark ?? 20) }}"
                                required
                                class="form-control @error('max_marks') is-invalid @enderror"
                                min="10" max="100" step="1">
                            @error('max_marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="pass_marks" class="form-label">Note de passage *</label>
                            <input type="number" name="pass_marks" id="pass_marks"
                                value="{{ old('pass_marks', $evaluation->pass_marks ?? $evaluation->weight ?? 10) }}"
                                required
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
                                placeholder="Description de l'évaluation, consignes, etc...">{{ old('description', $evaluation->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea name="instructions" id="instructions" rows="3"
                                class="form-control @error('instructions') is-invalid @enderror"
                                placeholder="Instructions particulières...">{{ old('instructions', $evaluation->instructions) }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Statut</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_planned" value="planned"
                                    {{ old('status', $evaluation->status ?? 'planned') == 'planned' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_planned">Planifiée</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_in_progress" value="in_progress"
                                    {{ old('status', $evaluation->status ?? 'planned') == 'in_progress' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_in_progress">En cours</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status_completed" value="completed"
                                    {{ old('status', $evaluation->status ?? 'planned') == 'completed' ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_completed">Terminée</label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Mettre à jour
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
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations actuelles</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">{{ substr($evaluation->title, 0, 1) }}</span>
                    </div>
                    <h6>{{ $evaluation->title }}</h6>
                    <p class="text-muted mb-1">{{ $evaluation->subject->name }} - {{ $evaluation->class->full_name ?? $evaluation->class->name }}</p>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td><strong>{{ $evaluation->examType->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Trimestre:</td>
                        <td><strong>{{ $evaluation->term->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date:</td>
                        <td><strong>{{ $evaluation->exam_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Notes saisies:</td>
                        <td><strong>{{ $evaluation->marks_count ?? 0 }}/{{ $evaluation->class->students_count ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Complétion:</td>
                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $evaluation->completion_percentage == 100 ? 'bg-success' : 'bg-warning' }}"
                                     style="width: {{ $evaluation->completion_percentage }}%">
                                </div>
                            </div>
                            <small class="text-muted">{{ $evaluation->completion_percentage }}%</small>
                        </td>
                    </tr>
                </table>
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
