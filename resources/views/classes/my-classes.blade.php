@extends('layouts.app')

@section('title', 'Mes Classes')
@section('page-title', 'Mes Classes Assignées')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de Bord</a></li>
<li class="breadcrumb-item active">Mes Classes</li>
@endsection

@section('content')
<div class="row">
    @forelse($assignedClasses as $class)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary">{{ $class->name }}</h6>
                <span class="badge bg-light text-dark">{{ $class->level }}</span>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h5 text-primary mb-1">{{ $class->students_count ?? 0 }}</div>
                        <small class="text-muted">Élèves</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 text-success mb-1">{{ $class->capacity }}</div>
                        <small class="text-muted">Capacité</small>
                    </div>
                </div>

                <!-- Indicateur de remplissage -->
                @if($class->capacity > 0)
                <div class="mb-3">
                    <label class="form-label small text-muted">Taux de remplissage</label>
                    <div class="progress" style="height: 6px;">
                        @php
                            $fillPercentage = min(100, (($class->students_count ?? 0) / $class->capacity) * 100);
                            $progressClass = $fillPercentage >= 90 ? 'bg-danger' : ($fillPercentage >= 75 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress-bar {{ $progressClass }}"
                             role="progressbar"
                             style="width: {{ $fillPercentage }}%">
                        </div>
                    </div>
                    <div class="text-end small text-muted">
                        {{ number_format($fillPercentage, 1) }}%
                    </div>
                </div>
                @endif

                <!-- Informations supplémentaires -->
                <div class="small text-muted mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Année scolaire:</span>
                        <span class="fw-semibold">{{ $class->schoolYear->year ?? 'N/A' }}</span>
                    </div>
                    @if($class->teacher)
                    <div class="d-flex justify-content-between">
                        <span>Titulaire:</span>
                        <span class="fw-semibold">{{ $class->teacher->name ?? $class->teacher->first_name . ' ' . $class->teacher->last_name }}</span>
                    </div>
                    @endif
                </div>

                <!-- Matières enseignées -->
                @php
                    $subjects = $class->teacherAssignments->where('teacher_id', auth()->id())->pluck('subject.name');
                @endphp
                @if($subjects->count() > 0)
                <div class="mb-3">
                    <label class="form-label small text-muted">Matières enseignées</label>
                    <div>
                        @foreach($subjects as $subject)
                            <span class="badge bg-info text-dark me-1 mb-1">{{ $subject }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            <div class="card-footer bg-white">
                <div class="d-grid gap-2">
                    <a href="{{ route('teacher.evaluations', ['class_id' => $class->id]) }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-clipboard-check me-1"></i>Évaluations
                    </a>
                    <a href="{{ route('admin.classes.show', $class) }}" 
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i>Voir détails
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-building display-1 text-muted"></i>
                <h4 class="text-muted mt-3">Aucune classe assignée</h4>
                <p class="text-muted">Vous n'êtes actuellement assigné à aucune classe.</p>
                <p class="text-muted small">Contactez l'administration pour être assigné à une classe.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Statistiques globales -->
@if($assignedClasses->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Vue d'ensemble</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="h3 text-primary mb-1">{{ $assignedClasses->count() }}</div>
                        <small class="text-muted">Classes assignées</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="h3 text-success mb-1">
                            {{ $assignedClasses->sum('students_count') }}
                        </div>
                        <small class="text-muted">Élèves total</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="h3 text-info mb-1">
                            {{ $assignedClasses->unique('level')->count() }}
                        </div>
                        <small class="text-muted">Niveaux différents</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        @php
                            $totalCapacity = $assignedClasses->sum('capacity');
                            $totalStudents = $assignedClasses->sum('students_count');
                            $globalFillRate = $totalCapacity > 0 ? round(($totalStudents / $totalCapacity) * 100, 1) : 0;
                        @endphp
                        <div class="h3 text-warning mb-1">{{ $globalFillRate }}%</div>
                        <small class="text-muted">Taux de remplissage moyen</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: 1px solid #e9ecef;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.progress {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.7em;
}
</style>
@endpush