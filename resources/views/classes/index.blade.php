@extends('layouts.app')

@section('title', 'Gestion des Classes')
@section('page-title', 'Gestion des Classes')

@section('breadcrumbs')
<li class="breadcrumb-item active">Classes</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Nouvelle classe
</a>
@endsection

@section('content')
<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.classes.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nom de la classe...">
            </div>
            <div class="col-md-4">
                <label for="level" class="form-label">Niveau</label>
                <select name="level" id="level" class="form-select">
                    <option value="">Tous les niveaux</option>
                    <option value="6ème" {{ request('level') == '6ème' ? 'selected' : '' }}>6ème</option>
                    <option value="5ème" {{ request('level') == '5ème' ? 'selected' : '' }}>5ème</option>
                    <option value="4ème" {{ request('level') == '4ème' ? 'selected' : '' }}>4ème</option>
                    <option value="3ème" {{ request('level') == '3ème' ? 'selected' : '' }}>3ème</option>
                    <option value="2nde" {{ request('level') == '2nde' ? 'selected' : '' }}>2nde</option>
                    <option value="1ère" {{ request('level') == '1ère' ? 'selected' : '' }}>1ère</option>
                    <option value="Terminale" {{ request('level') == 'Terminale' ? 'selected' : '' }}>Terminale</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des classes -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-building me-2"></i>Liste des classes
            <span class="badge bg-primary ms-2">{{ $classes->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $classes->currentPage() }} sur {{ $classes->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($classes->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th>Niveau</th>
                        <th>Année</th>
                        <th>Élèves</th>
                        <th>Titulaire</th>
                        <th>Statut</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary">{{ $class->name }}</div>
                            <small class="text-muted">{{ $class->level }}</small>
                        </td>
                        <td>{{ $class->level }}</td>
                        <td>{{ $class->schoolYear->year ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ $class->students_count ?? 0 }}/{{ $class->capacity }}
                            </span>
                        </td>
                        <td>
                            @if($class->teacher)
                                <span class="text-muted">{{ $class->teacher->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($class->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.classes.show', $class) }}"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.classes.edit', $class) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-building display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucune classe trouvée</h4>
            <p class="text-muted">Aucune classe ne correspond à vos critères de recherche.</p>
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Créer la première classe
            </a>
        </div>
        @endif
    </div>

    @if($classes->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $classes->firstItem() }} à {{ $classes->lastItem() }} sur {{ $classes->total() }} classes
            </div>
            {{ $classes->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
