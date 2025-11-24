@extends('layouts.app')

@section('title', 'Tableau de bord Enseignant Titulaire')
@section('page-title', 'Tableau de bord Enseignant Titulaire')

@section('breadcrumbs')
<li class="breadcrumb-item active">Titulaire</li>
@endsection

@section('content')
@if(!$class)
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">Aucune classe assignée</h4>
                <p class="text-muted">Vous n'êtes pas encore assigné comme enseignant titulaire d'une classe.</p>
                <a href="{{ route('profile') }}" class="btn btn-primary">Vérifier mon profil</a>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Élèves dans la classe</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_students'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-people fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Matières enseignées</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_subjects'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-journal-text fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Évals complétées</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_evaluations'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-clipboard-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Moyenne de classe</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $classStats['class_average'] ?? '-' }}/20</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-graph-up fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-house-door me-2"></i>Ma Classe - {{ $class->full_name }}</h6>
            </div>
            <div class="card-body">
                @if($classStats)
                <div class="row text-center mb-4">
                    <div class="col-md-4">
                        <div class="h3 text-primary">{{ $classStats['class_average'] }}/20</div>
                        <small class="text-muted">Moyenne</small>
                    </div>
                    <div class="col-md-4">
                        <div class="h3 text-success">{{ $classStats['success_rate'] }}%</div>
                        <small class="text-muted">Taux réussite</small>
                    </div>
                    <div class="col-md-4">
                        <div class="h3 text-info">{{ $classStats['total_students'] }}</div>
                        <small class="text-muted">Élèves</small>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Performance de la classe</small>
                        <small>{{ $classStats['max_average'] }}/20 max - {{ $classStats['min_average'] }}/20 min</small>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ ($classStats['class_average'] / 20) * 100 }}%"></div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune statistique disponible pour le moment.</p>
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('titular.my-class') }}" class="btn btn-primary w-100">
                        <i class="bi bi-eye me-1"></i>Voir le détail de ma classe
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Élèves en difficulté</h6>
            </div>
            <div class="card-body">
                @if($strugglingStudents->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($strugglingStudents->take(5) as $student)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $student->student->full_name }}</h6>
                                <small class="text-muted">{{ $student->student->matricule }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">{{ $student->average }}/20</span>
                                <div class="mt-1">
                                    <a href="{{ route('titular.students.show', $student->student) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($strugglingStudents->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('titular.students') }}" class="btn btn-sm btn-outline-primary">
                        Voir tous ({{ $strugglingStudents->count() }})
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="bi bi-emoji-smile text-success" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucun élève en difficulté identifié.</p>
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
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-lightning me-2"></i>Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <a href="{{ route('titular.evaluations.create') }}" class="btn btn-outline-primary w-100 h-100 py-3">
                            <i class="bi bi-plus-circle d-block mb-2" style="font-size: 1.5rem;"></i>Nouvelle évaluation
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('titular.students') }}" class="btn btn-outline-success w-100 h-100 py-3">
                            <i class="bi bi-people d-block mb-2" style="font-size: 1.5rem;"></i>Liste des élèves
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('titular.reports.class') }}" class="btn btn-outline-info w-100 h-100 py-3">
                            <i class="bi bi-graph-up d-block mb-2" style="font-size: 1.5rem;"></i>Rapport de classe
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('titular.evaluations') }}" class="btn btn-outline-warning w-100 h-100 py-3">
                            <i class="bi bi-clipboard-check d-block mb-2" style="font-size: 1.5rem;"></i>Évaluations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
