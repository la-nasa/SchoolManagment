@extends('layouts.app')

@section('title', 'Gestion des Matières')
@section('page-title', 'Gestion des Matières')

@section('breadcrumbs')
<li class="breadcrumb-item active">Matières</li>
@endsection

@section('page-actions')
@can('create-subjects')
<a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Nouvelle matière
</a>
@endcan
@endsection

@section('content')
<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Matières
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $subjects->total() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-text fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Matières Actives
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeSubjectsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Coefficient Moyen
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($averageCoefficient, 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.subjects.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nom, code ou description...">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactives</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des matières -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>Liste des matières
            <span class="badge bg-primary ms-2">{{ $subjects->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $subjects->currentPage() }} sur {{ $subjects->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($subjects->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matière</th>
                        <th>Code</th>
                        <th>Coefficient</th>
                        <th>Note Max</th>
                        <th>Enseignants</th>
                        <th>Statut</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <span class="text-white fw-bold small">{{ substr($subject->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $subject->name }}</div>
                                    @if($subject->description)
                                    <small class="text-muted">{{ $subject->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark font-monospace">{{ $subject->code }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold">{{ $subject->coefficient }}</span>
                                @if($subject->coefficient > $averageCoefficient)
                                <i class="bi bi-arrow-up text-success ms-1 small"></i>
                                @endif
                            </div>
                        </td>
                        <td>{{ $subject->max_mark }}/{{ $subject->max_mark }}</td>
                        <td>
                            <span class="badge bg-info">{{ $subject->teachers_count ?? 0 }}</span>
                        </td>
                        <td>
                            @if($subject->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.subjects.show', $subject) }}"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-subjects')
                                <a href="{{ route('admin.subjects.edit', $subject) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete-subjects')
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette matière ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
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
            <h4 class="text-muted mt-3">Aucune matière trouvée</h4>
            <p class="text-muted">
                @if(request()->hasAny(['search', 'status']))
                Aucune matière ne correspond à vos critères de recherche.
                @else
                Commencez par créer votre première matière.
                @endif
            </p>
            @can('create-subjects')
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Créer la première matière
            </a>
            @endcan
        </div>
        @endif
    </div>

    @if($subjects->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $subjects->firstItem() }} à {{ $subjects->lastItem() }} sur {{ $subjects->total() }} matières
            </div>
            {{ $subjects->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
