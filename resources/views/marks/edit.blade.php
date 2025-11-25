@extends('layouts.app')

@section('title', 'Modifier la Note')
@section('page-title', 'Modifier la Note')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.marks.index') }}">Notes</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.marks.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Modifier la note</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.marks.update', $mark) }}">
                    @csrf
                    @method('PUT')

                    <!-- Informations de l'élève -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Informations de l'élève</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                    <span class="text-white fw-bold">
                                        {{ substr($mark->student->first_name, 0, 1) }}{{ substr($mark->student->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $mark->student->first_name }} {{ $mark->student->last_name }}</div>
                                    <div class="text-muted">{{ $mark->student->matricule }} - {{ $mark->student->class->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de l'évaluation -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Informations de l'évaluation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong>Matière:</strong> {{ $mark->evaluation->subject->name ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Type:</strong> {{ $mark->evaluation->type ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Séquence:</strong> {{ $mark->evaluation->sequence_type ?? 'N/A' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>Note max:</strong> {{ $mark->evaluation->max_marks ?? 20 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails de la note -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="marks" class="form-label">Note *</label>
                            <input type="number" name="marks" id="marks"
                                   value="{{ old('marks', number_format($mark->marks, 2)) }}"
                                   step="0.25" min="0" max="{{ $mark->evaluation->max_marks ?? 20 }}"
                                   class="form-control @error('marks') is-invalid @enderror" required>
                            @error('marks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Note sur {{ $mark->evaluation->max_marks ?? 20 }}
                                @php
                                    $percentage = ($mark->marks / ($mark->evaluation->max_marks ?? 20)) * 20;
                                @endphp
                                ({{ number_format($percentage, 1) }}/20)
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="appreciation" class="form-label">Appréciation</label>
                            <select name="appreciation" id="appreciation"
                                    class="form-select @error('appreciation') is-invalid @enderror">
                                <option value="">Sélectionner</option>
                                <option value="Excellent" {{ old('appreciation', $mark->appreciation) == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="Très bien" {{ old('appreciation', $mark->appreciation) == 'Très bien' ? 'selected' : '' }}>Très bien</option>
                                <option value="Bien" {{ old('appreciation', $mark->appreciation) == 'Bien' ? 'selected' : '' }}>Bien</option>
                                <option value="Assez bien" {{ old('appreciation', $mark->appreciation) == 'Assez bien' ? 'selected' : '' }}>Assez bien</option>
                                <option value="Passable" {{ old('appreciation', $mark->appreciation) == 'Passable' ? 'selected' : '' }}>Passable</option>
                                <option value="Insuffisant" {{ old('appreciation', $mark->appreciation) == 'Insuffisant' ? 'selected' : '' }}>Insuffisant</option>
                            </select>
                            @error('appreciation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="comment" class="form-label">Commentaire</label>
                            <textarea name="comment" id="comment" rows="3"
                                      class="form-control @error('comment') is-invalid @enderror"
                                      placeholder="Commentaire additionnel...">{{ old('comment', $mark->comment) }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_absent" id="is_absent" value="1"
                                    {{ old('is_absent', $mark->is_absent) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="is_absent" class="form-check-label">Élève absent</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.marks.index') }}" class="btn btn-outline-secondary">
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
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Note actuelle:</td>
                        <td class="fw-bold">{{ number_format($mark->marks, 2) }}/{{ $mark->evaluation->max_marks ?? 20 }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Appréciation:</td>
                        <td>
                            @if($mark->appreciation)
                                <span class="badge
                                    {{ $mark->appreciation == 'Excellent' ? 'bg-success' : '' }}
                                    {{ $mark->appreciation == 'Très bien' ? 'bg-primary' : '' }}
                                    {{ $mark->appreciation == 'Bien' ? 'bg-info' : '' }}
                                    {{ $mark->appreciation == 'Assez bien' ? 'bg-warning' : '' }}
                                    {{ $mark->appreciation == 'Passable' ? 'bg-orange' : '' }}
                                    {{ $mark->appreciation == 'Insuffisant' ? 'bg-danger' : '' }}">
                                    {{ $mark->appreciation }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut:</td>
                        <td>
                            @if($mark->is_absent)
                                <span class="badge bg-warning">Absent</span>
                            @else
                                <span class="badge bg-success">Noté</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Saisie le:</td>
                        <td><strong>{{ $mark->created_at->format('d/m/Y H:i') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Modifiée le:</td>
                        <td><strong>{{ $mark->updated_at->format('d/m/Y H:i') }}</strong></td>
                    </tr>
                </table>

                @if($mark->comment)
                <div class="mt-3">
                    <label class="form-label text-muted">Commentaire actuel:</label>
                    <div class="border rounded p-2 bg-light">
                        {{ $mark->comment }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const markInput = document.getElementById('marks');
    const appreciationSelect = document.getElementById('appreciation');
    const absentCheckbox = document.getElementById('is_absent');

    function updateAppreciation() {
        const mark = parseFloat(markInput.value);
        const maxMark = {{ $mark->evaluation->max_marks ?? 20 }};
        const percentage = (mark / maxMark) * 20;

        if (isNaN(mark)) {
            appreciationSelect.value = '';
            return;
        }

        if (percentage >= 18) {
            appreciationSelect.value = 'Excellent';
        } else if (percentage >= 16) {
            appreciationSelect.value = 'Très bien';
        } else if (percentage >= 14) {
            appreciationSelect.value = 'Bien';
        } else if (percentage >= 12) {
            appreciationSelect.value = 'Assez bien';
        } else if (percentage >= 10) {
            appreciationSelect.value = 'Passable';
        } else {
            appreciationSelect.value = 'Insuffisant';
        }
    }

    function handleAbsentChange() {
        if (absentCheckbox.checked) {
            markInput.disabled = true;
            markInput.value = '0';
            appreciationSelect.value = '';
        } else {
            markInput.disabled = false;
        }
    }

    markInput.addEventListener('change', updateAppreciation);
    markInput.addEventListener('input', updateAppreciation);
    absentCheckbox.addEventListener('change', handleAbsentChange);

    // Initialiser l'état
    handleAbsentChange();
    updateAppreciation();
});
</script>
@endpush
