@extends('layouts.app')

@section('title', 'Détails de l\'Élève - ' . $student->full_name)
@section('page-title', 'Détails de l\'Élève')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Élèves</a></li>
<li class="breadcrumb-item active">{{ $student->full_name }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    @can('edit-students')
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Modifier
    </a>
    @endcan
    <a href="{{ route('admin.students.bulletin', $student) }}" class="btn btn-success">
        <i class="bi bi-file-text me-1"></i>Bulletin
    </a>
    <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="rounded-circle mb-3" width="120" height="120">
                <h4 class="card-title">{{ $student->full_name }}</h4>
                <p class="text-muted mb-2">{{ $student->matricule }}</p>
                <span class="badge bg-light text-dark fs-6">{{ $student->class->full_name }}</span>

                <div class="mt-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary mb-1">{{ $student->age }}</div>
                            <small class="text-muted">Âge</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1">
                                @if($student->gender == 'M') M @else F @endif
                            </div>
                            <small class="text-muted">Genre</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations personnelles</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" width="40%">Date de naissance:</td>
                        <td><strong>{{ $student->birth_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    @if($student->birth_place)
                    <tr>
                        <td class="text-muted">Lieu de naissance:</td>
                        <td><strong>{{ $student->birth_place }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Classe:</td>
                        <td><strong>{{ $student->class->full_name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Année scolaire:</td>
                        <td><strong>{{ $student->schoolYear->year }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut:</td>
                        <td>
                            @if($student->is_active)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Inscrit le:</td>
                        <td><strong>{{ $student->created_at->format('d/m/Y') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Performance académique</h6>
            </div>
            <div class="card-body">
                @if($student->generalAverages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Trimestre</th>
                                <th>Moyenne Générale</th>
                                <th>Rang</th>
                                <th>Appréciation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->generalAverages as $average)
                            <tr>
                                <td><strong>{{ $average->term->name }}</strong></td>
                                <td>
                                    <span class="h5 {{ $average->average >= 10 ? 'text-success' : 'text-danger' }}">
                                        {{ $average->average }}/20
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $average->rank }}/{{ $average->total_students }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $average->appreciation }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('students.bulletin', ['student' => $student, 'term_id' => $average->term_id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Bulletin
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune donnée de performance disponible.</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Notes par matière</h6>
            </div>
            <div class="card-body">
                @if($student->averages->count() > 0)
                <div class="row">
                    @foreach($student->averages->groupBy('term_id') as $termAverages)
                    @php $term = $termAverages->first()->term; @endphp
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2">{{ $term->name }}</h6>
                        <div class="row">
                            @foreach($termAverages as $average)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $average->subject->name }}</h6>
                                        <div class="h4 {{ $average->average >= 10 ? 'text-success' : 'text-danger' }} mb-2">
                                            {{ $average->average }}/20
                                        </div>
                                        <small class="text-muted">Coefficient: {{ $average->subject->coefficient }}</small>
                                        <div class="mt-2">
                                            <span class="badge bg-light text-dark">{{ $average->appreciation }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune note disponible.</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Dernières évaluations</h6>
            </div>
            <div class="card-body">
                @if($student->marks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Matière</th>
                                <th>Évaluation</th>
                                <th>Note</th>
                                <th>Appréciation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->marks->sortByDesc('evaluation.exam_date')->take(10) as $mark)
                            <tr>
                                <td><small>{{ $mark->evaluation->exam_date->format('d/m/Y') }}</small></td>
                                <td>{{ $mark->subject->name }}</td>
                                <td>
                                    <small>{{ $mark->evaluation->title }}</small>
                                    <br><span class="badge bg-secondary">{{ $mark->evaluation->examType->name }}</span>
                                </td>
                                <td>
                                    @if($mark->is_absent)
                                    <span class="badge bg-warning">Absent</span>
                                    @else
                                    <span class="fw-bold {{ $mark->marks >= ($mark->evaluation->max_marks / 2) ? 'text-success' : 'text-danger' }}">
                                        {{ $mark->marks }}/{{ $mark->evaluation->max_marks }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $mark->appreciation }}</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-clipboard-check text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune évaluation notée.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
