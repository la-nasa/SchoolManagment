@extends('layouts.app')

@section('title', 'Mes Évaluations')
@section('page-title', 'Mes Évaluations')

@section('breadcrumbs')
<li class="breadcrumb-item active">Mes Évaluations</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-clipboard-check me-2"></i>Liste de mes évaluations
        </h6>
        @can('create-evaluations')
        <a href="{{ route('teacher.evaluations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nouvelle évaluation
        </a>
        @endcan
    </div>
    <div class="card-body">
        @if($evaluations->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Note max</th>
                        <th>Statut</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluations as $evaluation)
                    <tr>
                        <td>{{ $evaluation->subject->name }}</td>
                        <td>{{ $evaluation->class->name }}</td>
                        <td>
                            <span class="badge bg-info">{{ $evaluation->examType->name }}</span>
                        </td>
                        <td>{{ $evaluation->evaluation_date->format('d/m/Y') }}</td>
                        <td>{{ $evaluation->max_marks }}</td>
                        <td>
                            @if($evaluation->is_published)
                            <span class="badge bg-success">Publiée</span>
                            @else
                            <span class="badge bg-warning">Brouillon</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('teacher.evaluations.show', $evaluation) }}" 
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('teacher.evaluations.edit', $evaluation) }}" 
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
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
            <i class="bi bi-clipboard-x display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucune évaluation</h4>
            <p class="text-muted">Vous n'avez pas encore créé d'évaluation.</p>
            @can('create-evaluations')
            <a href="{{ route('teacher.evaluations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Créer une évaluation
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection