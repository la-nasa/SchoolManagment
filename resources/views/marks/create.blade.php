@extends('layouts.app')

@section('title', 'Saisie des Notes - ' . $evaluation->title)
@section('page-title', 'Saisie des Notes')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.evaluations.index') }}">Évaluations</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.evaluations.show', $evaluation) }}">{{ $evaluation->title }}</a></li>
<li class="breadcrumb-item active">Saisie des notes</li>
@endsection

@section('page-actions')
<button type="button" class="btn btn-success" onclick="document.getElementById('marks-form').submit()">
    <i class="bi bi-check-circle me-1"></i>Enregistrer les notes
</button>
<a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i>Saisie des notes
        </h6>
    </div>
    <div class="card-body">
        <!-- Informations sur l'évaluation -->
        <div class="row mb-4">
            <div class="col-md-4">
                <strong>Évaluation:</strong> {{ $evaluation->title }}
            </div>
            <div class="col-md-3">
                <strong>Classe:</strong> {{ $evaluation->class->name ?? 'Classe inconnue' }}
            </div>
            <div class="col-md-3">
                <strong>Matière:</strong> {{ $evaluation->subject->name ?? 'Matière inconnue' }}
            </div>
            <div class="col-md-2">
                <strong>Date:</strong>
                @if($evaluation->exam_date)
                    {{ \Carbon\Carbon::parse($evaluation->exam_date)->format('d/m/Y') }}
                @else
                    Non définie
                @endif
            </div>
        </div>

        <form id="marks-form" action="{{ route('admin.marks.store') }}" method="POST">
            @csrf
            <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Élève</th>
                            <th width="120">Note /{{ $evaluation->max_marks ?? 20 }}</th>
                            <th width="100">Absent</th>
                            <th width="200">Commentaire</th>
                            <th width="100">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $existingMark = $existingMarks[$student->id] ?? null;
                            $isAbsent = $existingMark && $existingMark->is_absent;
                            $markValue = $existingMark && !$isAbsent ? $existingMark->marks : '';
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($student->photo_url)
                                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                                         class="rounded-circle me-2" width="32" height="32">
                                    @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                         style="width: 32px; height: 32px;">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</div>
                                        <small class="text-muted">{{ $student->matricule ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number"
                                       class="form-control mark-input"
                                       name="marks[{{ $student->id }}][mark]"
                                       value="{{ $markValue }}"
                                       min="0"
                                       max="{{ $evaluation->max_marks ?? 20 }}"
                                       step="0.25"
                                       {{ $isAbsent ? 'disabled' : '' }}
                                       placeholder="0.00">
                                <!-- CHAMP CACHÉ POUR student_id -->
                                <input type="hidden" name="marks[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input absent-checkbox"
                                           type="checkbox"
                                           name="marks[{{ $student->id }}][is_absent]"
                                           value="1"
                                           {{ $isAbsent ? 'checked' : '' }}
                                           data-student="{{ $student->id }}">
                                </div>
                            </td>
                            <td>
                                <input type="text"
                                       class="form-control"
                                       name="marks[{{ $student->id }}][comment]"
                                       value="{{ $existingMark->comment ?? '' }}"
                                       placeholder="Commentaire optionnel...">
                            </td>
                            <td class="text-center">
                                @if($existingMark)
                                    @if($existingMark->is_absent)
                                    <span class="badge bg-warning">Absent</span>
                                    @else
                                    <span class="badge bg-success">Noté</span>
                                    @endif
                                @else
                                <span class="badge bg-secondary">En attente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Instructions:</strong>
                    Saisissez les notes sur {{ $evaluation->max_marks ?? 20 }}. Cochez "Absent" si l'élève n'a pas composé.
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <small>{{ $students->count() }} élève(s) dans cette classe</small>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Enregistrer toutes les notes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des cases à cocher "Absent"
    const absentCheckboxes = document.querySelectorAll('.absent-checkbox');

    absentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const studentId = this.getAttribute('data-student');
            const markInput = document.querySelector(`input[name="marks[${studentId}][mark]"]`);

            if (this.checked) {
                markInput.disabled = true;
                markInput.value = '';
                markInput.placeholder = 'Absent';
            } else {
                markInput.disabled = false;
                markInput.placeholder = '0.00';
            }

            // Mettre à jour le statut visuellement
            updateStudentStatus(studentId, this.checked);
        });

        // Initialiser l'état des champs
        const studentId = checkbox.getAttribute('data-student');
        const markInput = document.querySelector(`input[name="marks[${studentId}][mark]"]`);
        if (checkbox.checked) {
            markInput.disabled = true;
            markInput.placeholder = 'Absent';
        }
    });

    function updateStudentStatus(studentId, isAbsent) {
        // Implémentez la mise à jour visuelle du statut si nécessaire
        console.log(`Étudiant ${studentId} - Absent: ${isAbsent}`);
    }

    // Validation des notes
    document.getElementById('marks-form').addEventListener('submit', function(e) {
        let hasErrors = false;
        const markInputs = document.querySelectorAll('.mark-input:not(:disabled)');

        markInputs.forEach(input => {
            const value = parseFloat(input.value);
            const maxMark = parseFloat(input.max);

            if (input.value !== '' && (isNaN(value) || value < 0 || value > maxMark)) {
                input.classList.add('is-invalid');
                hasErrors = true;

                // Message d'erreur
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = `La note doit être entre 0 et ${maxMark}`;

                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.parentNode.appendChild(errorDiv);
                }
            } else {
                input.classList.remove('is-invalid');
                const errorDiv = input.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                    errorDiv.remove();
                }
            }
        });

        if (hasErrors) {
            e.preventDefault();
            // Scroll vers la première erreur
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }
    });

    // Auto-format des notes
    const markInputs = document.querySelectorAll('.mark-input');
    markInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value !== '' && !isNaN(this.value)) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
});
</script>

<style>
.mark-input:disabled {
    background-color: #f8f9fa;
    opacity: 0.6;
    cursor: not-allowed;
}

.form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
}

.invalid-feedback {
    display: block;
}

.mark-input.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>
@endpush
