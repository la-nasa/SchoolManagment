@extends('layouts.app')

@section('title', 'Tableau de bord Administrateur')
@section('page-title', 'Tableau de bord Administrateur')

@section('breadcrumbs')
<li class="breadcrumb-item active">Administrateur</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-stats">
        <i class="bi bi-arrow-clockwise"></i> Actualiser
    </button>
</div>
@endsection

@section('content')
<!-- Statistiques rapides -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Classes Actives
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_classes'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Élèves Inscrits
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Enseignants
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_teachers'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Évaluations
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_evaluations'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistiques de l'établissement -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-up me-2"></i>Statistiques de l'établissement
                </h6>
            </div>
            <div class="card-body">
                @if($schoolStats && $schoolStats['total_students'] > 0)
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-primary">{{ number_format($schoolStats['school_average'], 2) }}/20</div>
                            <small class="text-muted">Moyenne générale</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-success">{{ number_format($schoolStats['success_rate'], 2) }}%</div>
                            <small class="text-muted">Taux de réussite</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-info">{{ $schoolStats['total_students'] }}</div>
                            <small class="text-muted">Élèves évalués</small>
                        </div>
                    </div>
                </div>

                <!-- Top 10 et Bottom 10 -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6 class="text-success">
                            <i class="bi bi-trophy me-1"></i>Top 10
                        </h6>
                        <div class="list-group">
                            @forelse(array_slice($schoolStats['top_10'], 0, 5) as $index => $average)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>#{{ $index + 1 }}</span>
                                <span class="badge bg-success rounded-pill">{{ number_format($average, 2) }}/20</span>
                            </div>
                            @empty
                            <div class="list-group-item text-center text-muted">
                                Aucune donnée disponible
                            </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">
                            <i class="bi bi-arrow-down me-1"></i>Bottom 10
                        </h6>
                        <div class="list-group">
                            @forelse(array_slice($schoolStats['bottom_10'], 0, 5) as $index => $average)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>#{{ $index + 1 }}</span>
                                <span class="badge bg-danger rounded-pill">{{ number_format($average, 2) }}/20</span>
                            </div>
                            @empty
                            <div class="list-group-item text-center text-muted">
                                Aucune donnée disponible
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune donnée statistique disponible pour le moment.</p>
                    <small class="text-muted">Les statistiques apparaîtront après la saisie des notes.</small>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Évaluations avec notes manquantes -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>Notes manquantes
                </h6>
            </div>
            <div class="card-body">
                @if($evaluationsWithMissingMarks->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($evaluationsWithMissingMarks as $evaluation)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $evaluation->title }}</h6>
                            <small class="text-warning">{{ 100 - $evaluation->completion_percentage }}%</small>
                        </div>
                        <p class="mb-1 small">
                            {{ $evaluation->class->name ?? 'Classe inconnue' }} - {{ $evaluation->subject->name ?? 'Matière inconnue' }}
                        </p>
                        <small class="text-muted">
                            @if($evaluation->exam_date)
                                {{ $evaluation->exam_date->format('d/m/Y') }}
                            @else
                                Date non définie
                            @endif
                        </small>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Toutes les évaluations sont complétées !</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning me-2"></i>Actions rapides
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="bi bi-person-plus d-block mb-2" style="font-size: 1.5rem;"></i>
                            Nouvel enseignant
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.classes.create') }}" class="btn btn-outline-success w-100 h-100 py-3">
                            <i class="bi bi-plus-circle d-block mb-2" style="font-size: 1.5rem;"></i>
                            Nouvelle classe
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.reports.school') }}" class="btn btn-outline-info w-100 h-100 py-3">
                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 1.5rem;"></i>
                            Rapports
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-warning w-100 h-100 py-3">
                            <i class="bi bi-shield-check d-block mb-2" style="font-size: 1.5rem;"></i>
                            Audit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('refresh-stats').addEventListener('click', function() {
    const btn = this;
    const originalHtml = btn.innerHTML;

    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Actualisation...';
    btn.disabled = true;

    // Simuler un rechargement des statistiques
    setTimeout(() => {
        location.reload();
    }, 1000);
});
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endpush
