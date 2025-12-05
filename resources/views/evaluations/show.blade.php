@extends('layouts.app')

@section('title', $evaluation->title)
@section('page-title', $evaluation->title)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.evaluations.index') }}">Évaluations</a></li>
    <li class="breadcrumb-item active">{{ $evaluation->title }}</li>
@endsection

@section('page-actions')
    <div class="btn-group">
        @can('edit-evaluations')
            <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
        @endcan
        @can('create-marks')
            <a href="{{ route('admin.marks.create', $evaluation) }}" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i>Saisir les notes
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations de l'Évaluation</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Matière</label>
                            <div class="fw-bold">{{ $evaluation->subject->name ?? 'Non spécifiée' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Classe</label>
                            <div class="fw-bold">
                                {{ $evaluation->class->full_name ?? ($evaluation->class->name ?? 'Non spécifiée') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Type</label>
                            <div class="fw-bold">{{ $evaluation->examType->name ?? 'Non spécifié' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Trimestre</label>
                            <div class="fw-bold">{{ $evaluation->term->name ?? 'Non spécifié' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Date</label>
                            <div class="fw-bold">
                                @if ($evaluation->exam_date)
                                    {{ $evaluation->exam_date->format('d/m/Y') }}
                                @else
                                    Non spécifiée
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Note maximale</label>
                            <div class="fw-bold">{{ $evaluation->max_marks ?? 20 }} points</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Note de passage</label>
                            <div class="fw-bold">{{ $evaluation->pass_marks ?? 10 }}</div>
                        </div>
                        @if ($evaluation->description)
                            <div class="col-12 mb-3">
                                <label class="form-label text-muted">Description</label>
                                <div class="fw-bold">{{ $evaluation->description }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notes des élèves -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-journal-text me-2"></i>Notes des Élèves
                        <span class="badge bg-primary ms-2">{{ $evaluation->marks->count() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if ($evaluation->marks && $evaluation->marks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Élève</th>
                                        <th width="100">Note</th>
                                        <th width="150">Appréciation</th>
                                        <th width="200">Remarques</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($evaluation->marks as $mark)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $mark->student->user->first_name ?? '' }}
                                                    {{ $mark->student->user->last_name ?? '' }}
                                                </div>
                                                <small class="text-muted">{{ $mark->student->matricule ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if ($mark->is_absent)
                                                    <span class="badge bg-warning">Absent</span>
                                                @else
                                                    <span
                                                        class="fw-bold">{{ $mark->marks }}/{{ $evaluation->max_marks ?? 20 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($mark->is_absent)
                                                    <span class="badge bg-warning">Absent</span>
                                                @else
                                                    <span
                                                        class="badge
                                            {{ $mark->marks >= 16 ? 'bg-success' : '' }}
                                            {{ $mark->marks >= 14 && $mark->marks < 16 ? 'bg-info' : '' }}
                                            {{ $mark->marks >= 12 && $mark->marks < 14 ? 'bg-primary' : '' }}
                                            {{ $mark->marks >= 10 && $mark->marks < 12 ? 'bg-warning' : '' }}
                                            {{ $mark->marks < 10 ? 'bg-danger' : '' }}">
                                                        {{ $mark->appreciation ?? '-' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $mark->comment ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @can('edit-marks')
                                                        <a href="{{ route('admin.marks.edit', $mark) }}"
                                                            class="btn btn-outline-primary" title="Modifier">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete-marks')
                                                        <form action="{{ route('admin.marks.destroy', $mark) }}" method="POST"
                                                            style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger"
                                                                title="Supprimer"
                                                                onclick="return confirm('Confirmer la suppression?')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-journal-text display-1 text-muted"></i>
                            <h4 class="text-muted mt-3">Aucune note</h4>
                            <p class="text-muted">Aucune note n'a été saisie pour cette évaluation.</p>
                            @can('create-marks')
                                <a href="{{ route('admin.marks.create', $evaluation) }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-pencil-square me-1"></i>Saisir les notes
                                </a>
                            @endcan
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
                    @php
                        $marksCount = $evaluation->marks ? $evaluation->marks->count() : 0;
                        $studentsCount =
                            $evaluation->class && $evaluation->class->students
                                ? $evaluation->class->students->count()
                                : 0;
                        $completionRate = $studentsCount > 0 ? ($marksCount / $studentsCount) * 100 : 0;
                        $averageMark =
                            $evaluation->marks && $evaluation->marks->count() > 0
                                ? $evaluation->marks->avg('marks')
                                : 0;
                        $maxMark =
                            $evaluation->marks && $evaluation->marks->count() > 0
                                ? $evaluation->marks->max('marks')
                                : 0;
                        $minMark =
                            $evaluation->marks && $evaluation->marks->count() > 0
                                ? $evaluation->marks->min('marks')
                                : 0;
                    @endphp

                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-primary mb-1">{{ $marksCount }}/{{ $studentsCount }}</div>
                            <small class="text-muted">Notes saisies</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 text-success mb-1">{{ number_format($completionRate, 0) }}%</div>
                            <small class="text-muted">Complétion</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-info mb-1">{{ number_format($averageMark, 2) }}</div>
                            <small class="text-muted">Moyenne</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-warning mb-1">{{ number_format($maxMark, 2) }}</div>
                            <small class="text-muted">Maximum</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enseignant -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Enseignant</h6>
                </div>
                <div class="card-body">
                    @if ($evaluation->class && $evaluation->class->teacher)
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px;">
                                <span class="text-white fw-bold">
                                    {{ substr($evaluation->class->teacher->name ?? 'E', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $evaluation->class->teacher->name ?? 'Non spécifié' }}</div>
                                <small class="text-muted">{{ $evaluation->class->teacher->email ?? '' }}</small>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="bi bi-person-x display-6"></i>
                            <p class="mt-2 mb-0">Aucun enseignant assigné</p>
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
                        @can('create-marks')
                            <a href="{{ route('admin.marks.create', $evaluation) }}" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-1"></i>Saisir les notes
                            </a>
                        @endcan
                        @can('edit-evaluations')
                            <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Modifier l'évaluation
                            </a>
                        @endcan
                        <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
