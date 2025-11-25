@extends('layouts.app')

@section('title', $subject->name)
@section('page-title', $subject->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Matières</a></li>
<li class="breadcrumb-item active">{{ $subject->name }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    @can('edit-subjects')
    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Modifier
    </a>
    @endcan
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations de la Matière</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Code</label>
                        <div class="fw-bold font-monospace">{{ $subject->code }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Coefficient</label>
                        <div class="fw-bold">{{ $subject->coefficient }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Note Maximale</label>
                        <div class="fw-bold">{{ $subject->max_mark }}/{{ $subject->max_mark }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Statut</label>
                        <div>
                            @if($subject->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    @if($subject->description)
                    <div class="col-12">
                        <label class="form-label text-muted">Description</label>
                        <div class="fw-bold">{{ $subject->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Enseignants assignés -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>Enseignants Assignés
                    <span class="badge bg-primary ms-2">{{ $subject->teachers_count ?? 0 }}</span>
                </h6>
            </div>
            <div class="card-body">
                @if($subject->teachers && $subject->teachers->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($subject->teachers as $teacher)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                <span class="text-white fw-bold">{{ substr($teacher->user->name ?? 'T', 0, 1) }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $teacher->user->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $teacher->matricule ?? 'N/A' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark">{{ $teacher->classes_count ?? 0 }} classe(s)</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-person-badge display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucun enseignant assigné</h4>
                    <p class="text-muted">Aucun enseignant n'est actuellement assigné à cette matière.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Statistiques -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistiques</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary mb-1">{{ $subject->teachers_count ?? 0 }}</div>
                        <small class="text-muted">Enseignants</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-success mb-1">{{ $subject->classes_count ?? 0 }}</div>
                        <small class="text-muted">Classes</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info mb-1">{{ $subject->evaluations_count ?? 0 }}</div>
                        <small class="text-muted">Évaluations</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ $subject->coefficient }}</div>
                        <small class="text-muted">Coefficient</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Évaluations récentes -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Évaluations Récentes</h6>
            </div>
            <div class="card-body">
                @if($subject->evaluations && $subject->evaluations->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($subject->evaluations->take(3) as $evaluation)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $evaluation->title }}</div>
                                <small class="text-muted">{{ $evaluation->class->name ?? 'N/A' }}</small>
                            </div>
                            <small class="text-muted">{{ $evaluation->exam_date->format('d/m/Y') ?? 'N/A' }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3">
                    <i class="bi bi-clipboard-check text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Aucune évaluation récente</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
