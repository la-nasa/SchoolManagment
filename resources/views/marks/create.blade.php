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
            <div class="row mb-4 p-3 bg-light rounded">
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
                    @if ($evaluation->exam_date)
                        {{ \Carbon\Carbon::parse($evaluation->exam_date)->format('d/m/Y') }}
                    @else
                        Non définie
                    @endif
                </div>
            </div>

            <!-- Formulaire de saisie -->
            <form id="marks-form" action="{{ route('admin.marks.store', $evaluation) }}" method="POST">
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
                            @forelse($students as $index => $student)
                                @php
                                    $existingMark = $existingMarks[$student->id] ?? null;
                                    $isAbsent = $existingMark && $existingMark->is_absent;
                                    $markValue = $existingMark && !$isAbsent ? $existingMark->marks : '';
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $student->user->first_name ?? '' }}
                                            {{ $student->user->last_name ?? '' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $student->matricule ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <input type="number" name="marks[{{ $student->id }}][marks]"
                                            class="form-control form-control-sm mark-input" value="{{ $markValue }}"
                                            min="0" max="{{ $evaluation->max_marks ?? 20 }}" step="0.5"
                                            {{ $isAbsent ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-center">
                                        <input type="hidden" name="marks[{{ $student->id }}][student_id]"
                                            value="{{ $student->id }}">
                                        <input type="checkbox" name="marks[{{ $student->id }}][is_absent]" value="1"
                                            class="form-check-input absent-checkbox" {{ $isAbsent ? 'checked' : '' }}
                                            onchange="toggleMark(this, {{ $student->id }})">
                                    </td>
                                    <td>
                                        <input type="text" name="marks[{{ $student->id }}][comment]"
                                            class="form-control form-control-sm" placeholder="Commentaire..."
                                            value="{{ $existingMark ? $existingMark->comment : '' }}">
                                    </td>
                                    <td class="text-center">
                                        @if ($existingMark)
                                            @if ($existingMark->is_absent)
                                                <span class="badge bg-warning">Absent</span>
                                            @else
                                                <span class="badge bg-success">Noté</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">Aucun étudiant trouvé</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Instructions:</strong>
                        Saisissez les notes sur {{ $evaluation->max_marks ?? 20 }}. Cochez "Absent" si l'élève n'a pas
                        composé.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>{{ $students->count() }} élève(s) dans cette classe</small>
                    </div>
                    <div class="gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Enregistrer les notes
                        </button>
                        <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Annuler
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleMark(checkbox, studentId) {
            const markInput = document.querySelector(`input[name="marks[${studentId}][marks]"]`);
            if (checkbox.checked) {
                markInput.disabled = true;
                markInput.value = '';
            } else {
                markInput.disabled = false;
            }
        }

        // Validation on form submit
        document.getElementById('marks-form').addEventListener('submit', function(e) {
            let hasData = false;
            const markInputs = document.querySelectorAll('.mark-input');
            const checkboxes = document.querySelectorAll('.absent-checkbox');

            for (let input of markInputs) {
                if (input.value) {
                    hasData = true;
                    break;
                }
            }

            for (let checkbox of checkboxes) {
                if (checkbox.checked) {
                    hasData = true;
                    break;
                }
            }

            if (!hasData) {
                e.preventDefault();
                alert('Veuillez saisir au moins une note ou marquer un élève absent.');
            }
        });
    </script>
@endpush
