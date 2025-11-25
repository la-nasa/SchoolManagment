@extends('layouts.app')

@section('title', $classe->name)
@section('page-title', $classe->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
<li class="breadcrumb-item active">{{ $classe->name }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    {{-- <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Modifier
    </a> --}}
    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Informations principales -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations de la Classe</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Niveau</label>
                        <div class="fw-bold">{{ $classe->level }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Année scolaire</label>
                        <div class="fw-bold">{{ $classe->schoolYear->year ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Capacité</label>
                        <div class="fw-bold">{{ $classe->capacity }} élèves</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Élèves inscrits</label>
                        <div class="fw-bold">{{ $classe->students_count ?? 0 }} élèves</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Enseignant titulaire</label>
                        <div class="fw-bold">{{ $classe->teacher->name ?? 'Aucun titulaire' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Statut</label>
                        <div>
                            @if($classe->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    @if($classe->description)
                    <div class="col-12">
                        <label class="form-label text-muted">Description</label>
                        <div class="fw-bold">{{ $classe->description }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Liste des élèves -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-people me-2"></i>Élèves de la Classe
                    <span class="badge bg-primary ms-2">{{ $classe->students_count ?? 0 }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @if($classe->students && $classe->students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Élève</th>
                                <th>Date de naissance</th>
                                <th>Contact parent</th>
                                <th class="text-end">Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classe->students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <span class="text-white small fw-bold">
                                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</div>
                                            <small class="text-muted">{{ $student->matricule }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($student->date_of_birth)
                                        <div>{{ $student->date_of_birth->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $student->date_of_birth->age }} ans</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->parent_phone)
                                        <div>{{ $student->parent_phone }}</div>
                                        <small class="text-muted">{{ $student->parent_email ?? 'N/A' }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    {{ number_format($student->average_mark ?? 0, 2) }}/20
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">Aucun élève</h4>
                    <p class="text-muted">Aucun élève n'est inscrit dans cette classe.</p>
                    <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Ajouter un élève
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Statistiques -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistiques</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary mb-1">{{ $classe->students_count ?? 0 }}</div>
                        <small class="text-muted">Élèves</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-success mb-1">{{ number_format($classStats['average'] ?? 0, 2) }}</div>
                        <small class="text-muted">Moyenne</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info mb-1">{{ number_format($classStats['min'] ?? 0, 2) }}</div>
                        <small class="text-muted">Minimum</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ number_format($classStats['max'] ?? 0, 2) }}</div>
                        <small class="text-muted">Maximum</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enseignants -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Enseignants</h6>
            </div>
            <div class="card-body">
                @if($classe->teacherAssignments && $classe->teacherAssignments->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($classe->teacherAssignments as $assignment)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <span class="text-white small fw-bold">
                                    {{ substr($assignment->teacher->name ?? 'T', 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $assignment->teacher->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $assignment->subject->name ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-3">
                    <i class="bi bi-person-badge text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Aucun enseignant assigné</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Ajouter un élève
                    </a>
                    <a href="{{ route('admin.evaluations.create', ['class_id' => $classe->id]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-clipboard-check me-1"></i>Nouvelle évaluation
                    </a>
                    <a href="{{ route('admin.reports.bulletin', ['class_id' => $classe->id , 'student' => $classe->students ]) }}"class="btn btn-outline-secondary">
                        <i class="bi bi-file-text me-1"></i>Générer bulletins
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
