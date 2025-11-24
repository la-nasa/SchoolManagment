@extends('layouts.app')

@section('title', 'Rapport de Performance')
@section('page-title', 'Rapport de Performance')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.school') }}">Rapports</a></li>
<li class="breadcrumb-item active">Performance</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.reports.performance', ['export' => true]) }}" class="btn btn-success">
        <i class="bi bi-file-pdf me-1"></i>Exporter PDF
    </a>
    <a href="{{ route('admin.reports.school') }}" class="btn btn-outline-secondary">
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
                    <i class="bi bi-graph-up me-2"></i>Performance Générale - {{ $term->name }} {{ $schoolYear->year }}
                </h6>
            </div>
            <div class="card-body">
                @if($schoolStats)
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-primary mb-2">{{ $schoolStats['school_average'] }}/20</div>
                            <div class="text-muted">Moyenne Générale</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-4 bg-light">
                            <div class="h2 text-success mb-2">{{ $schoolStats['success_rate'] }}%</div>
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
                            <div class="h2 text-warning mb-2">{{ count($topStudents) + count($bottomStudents) }}</div>
                            <div class="text-muted">Élèves Classés</div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucune donnée disponible</h4>
                    <p class="text-muted">Les statistiques de performance ne sont pas encore disponibles pour cette période.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Top 10 Élèves -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bi bi-trophy me-2"></i>Top 10 - Meilleurs Élèves
                </h6>
            </div>
            <div class="card-body">
                @if($topStudents && $topStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Élève</th>
                                <th>Classe</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topStudents as $student)
                            <tr>
                                <td>
                                    <span class="badge bg-success">#{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $student->student->user->photo_url ?? '/images/default-avatar.png' }}" 
                                             alt="{{ $student->student->user->name }}" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">{{ $student->student->user->name }}</div>
                                            <small class="text-muted">{{ $student->student->matricule }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->classe->name }}</td>
                                <td>
                                    <span class="badge bg-success rounded-pill">{{ $student->average }}/20</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-trophy text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucun élève dans le top 10.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom 10 Élèves -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>Bottom 10 - Élèves en Difficulté
                </h6>
            </div>
            <div class="card-body">
                @if($bottomStudents && $bottomStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Rang</th>
                                <th>Élève</th>
                                <th>Classe</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bottomStudents as $student)
                            <tr>
                                <td>
                                    <span class="badge bg-danger">#{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $student->student->user->photo_url ?? '/images/default-avatar.png' }}" 
                                             alt="{{ $student->student->user->name }}" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <div class="fw-semibold">{{ $student->student->user->name }}</div>
                                            <small class="text-muted">{{ $student->student->matricule }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->classe->name }}</td>
                                <td>
                                    <span class="badge bg-danger rounded-pill">{{ $student->average }}/20</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-exclamation-triangle text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucun élève en difficulté identifié.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Analyse des Performances -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>Analyse des Performances
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-success mb-2">{{ $topStudents ? $topStudents->count() : 0 }}</div>
                            <div class="text-muted">Élèves Excellents</div>
                            <small class="text-success">(≥ 14/20)</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-warning mb-2">
                                @php
                                    $averageStudents = $schoolStats['total_students'] - ($topStudents ? $topStudents->count() : 0) - ($bottomStudents ? $bottomStudents->count() : 0);
                                @endphp
                                {{ max(0, $averageStudents) }}
                            </div>
                            <div class="text-muted">Élèves Moyens</div>
                            <small class="text-warning">(10-14/20)</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="h4 text-danger mb-2">{{ $bottomStudents ? $bottomStudents->count() : 0 }}</div>
                            <div class="text-muted">Élèves en Difficulté</div>
                            <small class="text-danger">(< 10/20)</small>
                        </div>
                    </div>
                </div>

                <!-- Graphique de distribution -->
                <div class="mt-4">
                    <h6 class="mb-3">Distribution des Moyennes</h6>
                    <div class="row text-center">
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-danger">0-5</div>
                                <div class="small text-muted">Très Faible</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-warning">5-10</div>
                                <div class="small text-muted">Faible</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-info">10-12</div>
                                <div class="small text-muted">Passable</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-primary">12-14</div>
                                <div class="small text-muted">Assez Bien</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-success">14-16</div>
                                <div class="small text-muted">Bien</div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="border rounded p-2">
                                <div class="h6 mb-1 text-success">16-20</div>
                                <div class="small text-muted">Très Bien</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection