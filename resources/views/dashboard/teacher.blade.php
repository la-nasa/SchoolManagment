@extends('layouts.app')

@section('title', 'Tableau de bord Enseignant')
@section('page-title', 'Tableau de bord Enseignant')

@section('breadcrumbs')
<li class="breadcrumb-item active">Enseignant</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Classes Assignées</div>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Matières</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_subjects'] }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-journal-text fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Évals en attente</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingEvaluations->count() }}</div>
                    </div>
                    <div class="col-auto"><i class="bi bi-clock fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-building me-2"></i>Mes classes</h6>
                <span class="badge bg-primary">{{ $assignedClasses->count() }}</span>
            </div>
            <div class="card-body">
                @if($assignedClasses->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($assignedClasses as $class)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $class->full_name }}</h6>
                                <p class="mb-1 small text-muted">{{ $class->students->count() }} élèves</p>
                            </div>
                            <div class="text-end">
                                @php
                                    $classSubjects = $assignedSubjects->filter(function($subject) use ($class) {
                                        return $subject->pivot->class_id == $class->id;
                                    });
                                @endphp
                                <small class="text-muted d-block">{{ $classSubjects->count() }} matière(s)</small>
                                <a href="{{ route('teacher.evaluations', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary mt-1">Voir</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune classe assignée pour le moment.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-warning"><i class="bi bi-clock me-2"></i>Évaluations à compléter</h6>
                <span class="badge bg-warning">{{ $pendingEvaluations->count() }}</span>
            </div>
            <div class="card-body">
                @if($pendingEvaluations->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($pendingEvaluations->take(5) as $evaluation)
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <h6 class="mb-1">{{ $evaluation->title }}</h6>
                                <p class="mb-1 small">{{ $evaluation->class->name }} - {{ $evaluation->subject->name }}</p>
                                <small class="text-muted">
                                    {{ $evaluation->exam_date->format('d/m/Y') }} • Complétion: {{ number_format($evaluation->completion_percentage, 1) }}%
                                </small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('teacher.marks.create', $evaluation) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil me-1"></i>Saisir les notes
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($pendingEvaluations->count() > 5)
                <div class="text-center mt-3">
                    <a href="{{ route('teacher.evaluations') }}" class="btn btn-sm btn-outline-primary">
                        Voir toutes ({{ $pendingEvaluations->count() }})
                    </a>
                </div>
                @endif
                @else
                <div class="text-center py-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Toutes les évaluations sont complétées !</p>
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
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-journal-text me-2"></i>Matières enseignées</h6>
            </div>
            <div class="card-body">
                @if($assignedSubjects->count() > 0)
                <div class="row">
                    @foreach($assignedSubjects as $subject)
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card border">
                            <div class="card-body text-center">
                                <h5 class="card-title">{{ $subject->name }}</h5>
                                <p class="card-text text-muted small">Coefficient: {{ $subject->coefficient }}</p>
                                <span class="badge bg-light text-dark">{{ $subject->code }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune matière assignée pour le moment.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
