@extends('layouts.app')

@section('title', 'Gestion des Évaluations')
@section('page-title', 'Gestion des Évaluations')

@section('breadcrumbs')
<li class="breadcrumb-item active">Évaluations</li>
@endsection

@section('page-actions')
@can('create-evaluations')
<a href="{{ route('admin.evaluations.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Nouvelle évaluation
</a>
@endcan
@endsection

@section('content')
<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.evaluations.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="class_id" class="form-label">Classe</label>
                <select class="form-select" id="class_id" name="class_id">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->full_name ?? $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="subject_id" class="form-label">Matière</label>
                <select class="form-select" id="subject_id" name="subject_id">
                    <option value="">Toutes les matières</option>
                    @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="term_id" class="form-label">Trimestre</label>
                <select class="form-select" id="term_id" name="term_id">
                    <option value="">Tous les trimestres</option>
                    @foreach($terms as $term)
                    <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                        {{ $term->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des évaluations -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-check me-2"></i>Liste des évaluations
            <span class="badge bg-primary ms-2">{{ $evaluations->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $evaluations->currentPage() }} sur {{ $evaluations->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($evaluations->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Notes max</th>
                        <th>Complétion</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluations as $evaluation)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $evaluation->title }}</div>
                            @if($evaluation->description)
                            <small class="text-muted">{{ $evaluation->description }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $evaluation->class->full_name ?? $evaluation->class->name }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $evaluation->subject->name }}</span>
                            <br>
                            <small class="text-muted">Coef. {{ $evaluation->subject->coefficient }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $evaluation->examType->name }}</span>
                        </td>
                        <td>
                            {{ $evaluation->exam_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $evaluation->exam_date->diffForHumans() }}</small>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $evaluation->max_marks }}/20</span>
                            <br>
                            <small class="text-muted">Moy: {{ $evaluation->pass_marks }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                    <div class="progress-bar {{ $evaluation->completion_percentage == 100 ? 'bg-success' : 'bg-warning' }}"
                                         role="progressbar"
                                         style="width: {{ $evaluation->completion_percentage }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $evaluation->completion_percentage }}%</small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.evaluations.show', $evaluation) }}"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-evaluations')
                                <a href="{{ route('admin.evaluations.edit', $evaluation) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('create-marks')
                                <a href="{{ route('admin.marks.create', $evaluation) }}"
                                   class="btn btn-outline-success" title="Saisir notes">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-clipboard-check display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucune évaluation trouvée</h4>
            <p class="text-muted">Aucune évaluation ne correspond à vos critères de recherche.</p>
            @can('create-evaluations')
            <a href="{{ route('admin.evaluations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Créer la première évaluation
            </a>
            @endcan
        </div>
        @endif
    </div>

    @if($evaluations->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $evaluations->firstItem() }} à {{ $evaluations->lastItem() }} sur {{ $evaluations->total() }} évaluations
            </div>
            {{ $evaluations->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#class_id, #subject_id, #term_id').select2({
        placeholder: 'Sélectionnez une option',
        allowClear: true
    });
});
</script>
@endpush
