@extends('layouts.app')

@section('title', 'Tableau de bord Directeur')
@section('page-title', 'Tableau de bord Directeur')

@section('breadcrumbs')
<li class="breadcrumb-item active">Directeur</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Classes Actives</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_classes'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-building fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Élèves</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-people fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Enseignants</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_teachers'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-person-badge fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Taux Réussite</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $schoolStats['success_rate'] ?? 0 }}%</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-graph-up fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-bar-chart me-2"></i>Performance par Classe</h6>
            </div>
            <div class="card-body">
                @if($classStatistics)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Classe</th>
                                <th>Moyenne</th>
                                <th>Taux de Réussite</th>
                                <th>Élèves</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classStatistics as $className => $stats)
                            <tr>
                                <td class="fw-semibold">{{ $className }}</td>
                                <td><span class="badge bg-primary">{{ $stats['average'] }}/20</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress grow me-2" style="height: 8px;">
                                            <div class="progress-bar {{ $stats['success_rate'] >= 80 ? 'bg-success' : ($stats['success_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $stats['success_rate'] }}%"></div>
                                        </div>
                                        <span class="text-muted small">{{ $stats['success_rate'] }}%</span>
                                    </div>
                                </td>
                                <td>{{ $stats['total_students'] }}</td>
                                <td>
                                    @if($stats['average'] >= 12)
                                    <span class="badge bg-success">Excellente</span>
                                    @elseif($stats['average'] >= 10)
                                    <span class="badge bg-warning">Satisfaisante</span>
                                    @else
                                    <span class="badge bg-danger">À améliorer</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-bar-chart text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune donnée de performance disponible.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-info"><i class="bi bi-info-circle me-2"></i>Aperçu de l'établissement</h6>
            </div>
            <div class="card-body">
                @if($schoolStats)
                <div class="text-center mb-4">
                    <div class="h2 text-primary mb-2">{{ $schoolStats['school_average'] }}/20</div>
                    <div class="text-muted">Moyenne générale</div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Taux de réussite</small>
                        <small>{{ $schoolStats['success_rate'] }}%</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $schoolStats['success_rate'] }}%"></div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-6">
                        <div class="h5 mb-1 text-success">{{ $schoolStats['total_students'] }}</div>
                        <small class="text-muted">Élèves évalués</small>
                    </div>
                    <div class="col-6">
                        <div class="h5 mb-1 text-primary">{{ $stats['total_classes'] }}</div>
                        <small class="text-muted">Classes actives</small>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune statistique disponible.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-building me-2"></i>Classes de l'établissement</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($classes as $class)
                    <div class="col-md-4 col-lg-3 mb-3">
                        <div class="card border h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title">{{ $class->full_name }}</h6>
                                <div class="h4 text-primary mb-2">
                                    @php
                                        $classAverage = $class->generalAverages->where('term_id', $currentTerm->id)->avg('average');
                                    @endphp
                                    {{ $classAverage ? number_format($classAverage, 1) : '-' }}/20
                                </div>
                                <div class="small text-muted">
                                    {{ $class->students->count() }} élèves
                                </div>
                                @if($class->teacher)
                                <div class="mt-2">
                                    <small class="text-muted">Titulaire: {{ $class->teacher->name }}</small>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white text-center p-2">
                                <a href="{{ route('director.classes.show', $class) }}" class="btn btn-sm btn-outline-primary">Détails</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
