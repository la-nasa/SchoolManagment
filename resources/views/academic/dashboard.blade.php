@extends('layouts.app')

@section('title', 'Tableau de bord académique')
@section('page-title', 'Tableau de bord académique')

@section('breadcrumbs')
<li class="breadcrumb-item active">Tableau de bord</li>
@endsection

@section('content')
<!-- Header Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Élèves
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
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Taux de réussite
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['success_rate'], 1) }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
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
                            Moyenne générale
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['average_mark'], 2) }}/20</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-star fa-2x text-gray-300"></i>
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

<!-- Performance des Classes -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-building me-2"></i>Performance par Classe</h6>
    </div>
    <div class="card-body">
        @if ($classPerformance->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th>Élèves</th>
                        <th>Moyenne</th>
                        <th>Taux réussite</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($classPerformance as $class)
                    <tr>
                        <td class="fw-semibold">{{ $class->name }}</td>
                        <td>{{ $class->students_count }}</td>
                        <td>
                            <span class="badge bg-primary">
                                {{ number_format($class->average_mark, 2) }}/20
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $class->success_rate }}%"
                                         aria-valuenow="{{ $class->success_rate }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <span class="small">{{ number_format($class->success_rate, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-building display-1 text-muted"></i>
            <p class="text-muted mt-2">Aucune classe disponible</p>
        </div>
        @endif
    </div>
</div>

<!-- Performance par Matière -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-book me-2"></i>Performance par Matière</h6>
    </div>
    <div class="card-body">
        @if ($subjectPerformance->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Coefficient</th>
                        <th>Moyenne</th>
                        <th>Évaluations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjectPerformance as $subject)
                    <tr>
                        <td class="fw-semibold">{{ $subject->name }}</td>
                        <td>{{ $subject->coefficient }}</td>
                        <td>
                            <span class="badge bg-info">
                                {{ number_format($subject->average_mark, 2) }}/20
                            </span>
                        </td>
                        <td>{{ $subject->evaluations_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-book display-1 text-muted"></i>
            <p class="text-muted mt-2">Aucune matière disponible</p>
        </div>
        @endif
    </div>
</div>

<!-- Activités récentes -->
<div class="card">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Activités Récentes</h6>
    </div>
    <div class="card-body">
        @if ($recentActivities->count() > 0)
        <div class="list-group list-group-flush">
            @foreach ($recentActivities as $activity)
            <div class="list-group-item px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $activity->user?->name ?? 'Système' }}</div>
                        <div class="text-muted small">{{ $activity->description }}</div>
                    </div>
                    <div class="text-muted small text-nowrap ms-3">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-clock display-1 text-muted"></i>
            <p class="text-muted mt-2">Aucune activité récente</p>
        </div>
        @endif
    </div>
</div>
@endsection
