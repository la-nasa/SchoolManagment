@extends('layouts.app')

@section('title', 'Rapport de l\'Établissement')
@section('page-title', 'Rapport de l\'Établissement')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.school') }}">Rapports</a></li>
<li class="breadcrumb-item active">Établissement</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.reports.school', ['export' => true]) }}" class="btn btn-success">
        <i class="bi bi-file-pdf me-1"></i>Exporter PDF
    </a>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Statistiques principales -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>Statistiques Générales - {{ $term->name }} {{ $schoolYear->year }}
                </h6>
            </div>
            <div class="card-body">
                @if($stats['total_students'] > 0)
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-primary mb-2">{{ number_format($schoolStats['school_average'], 2) }}/20</div>
                            <div class="text-muted">Moyenne Générale</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-success mb-2">{{ number_format($schoolStats['success_rate'], 1) }}%</div>
                            <div class="text-muted">Taux de Réussite</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-info mb-2">{{ $schoolStats['total_students'] }}</div>
                            <div class="text-muted">Élèves Évalués</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-warning mb-2">{{ count($classes) }}</div>
                            <div class="text-muted">Classes Actives</div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucune donnée disponible</h4>
                    <p class="text-muted">Les statistiques ne sont pas encore disponibles pour cette période.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Performance par classe -->
    @if($stats && isset($schoolStats['class_statistics']) && count($schoolStats['class_statistics']) > 0)
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Performance par Classe
                </h6>
            </div>
            <div class="card-body">
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
                            @foreach($schoolStats['class_statistics'] as $className => $stats)
                            <tr>
                                <td class="fw-semibold">{{ $className }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ number_format($stats['average'], 2) }}/20</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress grow me-2" style="height: 8px;">
                                            <div class="progress-bar {{ $stats['success_rate'] >= 80 ? 'bg-success' : ($stats['success_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $stats['success_rate'] }}%">
                                            </div>
                                        </div>
                                        <span class="text-muted small">{{ number_format($stats['success_rate'], 1) }}%</span>
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
            </div>
        </div>
    </div>
    @endif

    <!-- Top et Bottom 10 -->
    @if($stats && count($stats['top_10']) > 0)
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-trophy me-2"></i>Classements
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h6 class="text-success">
                            <i class="bi bi-arrow-up me-1"></i>Top 10 Élèves
                        </h6>
                        <div class="list-group list-group-flush">
                            @foreach(array_slice($schoolStats['top_10'], 0, 5) as $index => $average)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">#{{ $index + 1 }}</span>
                                </div>
                                <span class="badge bg-success rounded-pill">{{ number_format($average, 2) }}/20</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12">
                        <h6 class="text-danger">
                            <i class="bi bi-arrow-down me-1"></i>Bottom 10 Élèves
                        </h6>
                        <div class="list-group list-group-flush">
                            @foreach(array_slice($schoolStats['bottom_10'], 0, 5) as $index => $average)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">#{{ $index + 1 }}</span>
                                </div>
                                <span class="badge bg-danger rounded-pill">{{ number_format($average, 2) }}/20</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Détails par classe -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Détails par Classe
                </h6>
            </div>
            <div class="card-body">
                @if(count($classes) > 0)
                <div class="row">
                    @foreach($classes as $class)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">{{ $class['classe']->name ?? 'Classe sans nom' }}</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $classStats = $class['stats'] ?? null;
                                    $classAverage = $classStats['average'] ?? 0;
                                    $successRate = $classStats['success_rate'] ?? 0;
                                    $totalStudents = $classStats['total_students'] ?? 0;
                                    $successCount = $totalStudents > 0 ? round(($successRate / 100) * $totalStudents) : 0;
                                @endphp

                                <div class="text-center mb-3">
                                    <div class="h3 text-primary">{{ number_format($classAverage, 2) }}/20</div>
                                    <small class="text-muted">Moyenne de classe</small>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Taux de réussite</small>
                                        <small>{{ number_format($successRate, 1) }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $successRate >= 80 ? 'bg-success' : ($successRate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                             style="width: {{ $successRate }}%">
                                        </div>
                                    </div>
                                </div>

                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="h5 mb-1 text-success">{{ $successCount }}</div>
                                        <small class="text-muted">Réussites</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="h5 mb-1 text-danger">{{ $totalStudents - $successCount }}</div>
                                        <small class="text-muted">Échecs</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white text-center">
                                <a href="{{ route('admin.classes.show', $class['classe']->id) }}" class="btn btn-sm btn-outline-primary">
                                    Détails de la classe
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-list-ul display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucune classe disponible</h4>
                    <p class="text-muted">Aucune donnée de classe n'est disponible pour cette période.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
