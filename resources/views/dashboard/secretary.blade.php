@extends('layouts.app')

@section('title', 'Tableau de bord Secrétaire')
@section('page-title', 'Tableau de bord Secrétaire')

@section('breadcrumbs')
<li class="breadcrumb-item active">Secrétaire</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Élèves Inscrits</div>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Classes Actives</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_classes'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-building fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Nouveaux cette année</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['new_students_this_year'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-person-plus fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Documents à générer</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $recentEvaluations->count() }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-file-text fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock me-2"></i>Évaluations Récentes</h6>
            </div>
            <div class="card-body">
                @if($recentEvaluations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Évaluation</th>
                                <th>Classe</th>
                                <th>Matière</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvaluations as $evaluation)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $evaluation->title }}</div>
                                    <small class="text-muted">{{ $evaluation->examType->name }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $evaluation->class->full_name }}</span>
                                </td>
                                <td>{{ $evaluation->subject->name }}</td>
                                <td>
                                    <small>{{ $evaluation->exam_date->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('secretary.evaluations.pv', $evaluation) }}" class="btn btn-outline-primary" title="Générer PV">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                        <a href="{{ route('evaluations.show', $evaluation) }}" class="btn btn-outline-secondary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-clipboard-check text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune évaluation récente.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-lightning me-2"></i>Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('secretary.students.create') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-person-plus me-2"></i>Nouvel élève
                    </a>
                    <a href="{{ route('secretary.classes') }}" class="btn btn-success btn-lg">
                        <i class="bi bi-building me-2"></i>Gestion des classes
                    </a>
                    <a href="{{ route('secretary.reports.school') }}" class="btn btn-info btn-lg">
                        <i class="bi bi-graph-up me-2"></i>Rapports statistiques
                    </a>
                    <a href="{{ route('secretary.archives.students') }}" class="btn btn-warning btn-lg">
                        <i class="bi bi-archive me-2"></i>Archives élèves
                    </a>
                </div>
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
                    @php $classesList = \App\Models\Classe::active()->withCount('students')->get(); @endphp
                    @foreach($classesList as $classItem)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title">{{ $classItem->full_name }}</h6>
                                <div class="h4 text-primary mb-2">{{ $classItem->students_count }}</div>
                                <small class="text-muted">élèves</small>
                                @if($classItem->teacher)
                                <div class="mt-2">
                                    <small class="text-muted d-block">Titulaire:</small>
                                    <small class="fw-semibold">{{ $classItem->teacher->name }}</small>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white text-center p-2">
                                <div class="btn-group w-100">
                                    <a href="{{ route('secretary.classes.show', $classItem) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                    <a href="{{ route('secretary.classes.bulletins', $classItem) }}" class="btn btn-sm btn-outline-success">Bulletins</a>
                                </div>
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
