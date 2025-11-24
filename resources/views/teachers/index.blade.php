@extends('layouts.app')

@section('title', 'Gestion des Enseignants')
@section('page-title', 'Gestion des Enseignants')

@section('breadcrumbs')
<li class="breadcrumb-item active">Enseignants</li>
@endsection

@section('page-actions')
@can('create-users')
<a href="{{ route('admin.users.create') }}" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Nouvel enseignant
</a>
@endcan
@endsection

@section('content')
<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nom, email ou matricule...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des enseignants -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people me-2"></i>Liste des enseignants
            <span class="badge bg-primary ms-2">{{ $teachers->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $teachers->currentPage() }} sur {{ $teachers->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($teachers->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matricule</th>
                        <th>Enseignant</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Classe titulaire</th>
                        <th>Statut</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                    <tr>
                        <td>
                            <span class="fw-bold text-primary">{{ $teacher->matricule }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->name }}"
                                     class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <div class="fw-semibold">{{ $teacher->name }}</div>
                                    <small class="text-muted">Inscrit le {{ $teacher->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($teacher->isTitularTeacher())
                            <span class="badge bg-success">Enseignant titulaire</span>
                            @elseif($teacher->isTeacher())
                            <span class="badge bg-info">Enseignant</span>
                            @endif
                        </td>
                        <td>
                            <a href="mailto:{{ $teacher->email }}" class="text-decoration-none">
                                {{ $teacher->email }}
                            </a>
                        </td>
                        <td>
                            @if($teacher->phone)
                            <a href="tel:{{ $teacher->phone }}" class="text-decoration-none">
                                {{ $teacher->phone }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($teacher->class)
                            <span class="badge bg-light text-dark">{{ $teacher->class->full_name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($teacher->is_active)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.users.show', $teacher) }}"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-users')
                                <a href="{{ route('admin.users.edit', $teacher) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('edit-users')
                                <form action="{{ route('admin.users.reset-password', $teacher) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Réinitialiser le mot de passe de cet enseignant ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning" title="Réinitialiser mot de passe">
                                        <i class="bi bi-key"></i>
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
            <i class="bi bi-person-badge display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucun enseignant trouvé</h4>
            <p class="text-muted">Aucun enseignant ne correspond à vos critères de recherche.</p>
            @can('create-users')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Ajouter le premier enseignant
            </a>
            @endcan
        </div>
        @endif
    </div>

    @if($teachers->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $teachers->firstItem() }} à {{ $teachers->lastItem() }} sur {{ $teachers->total() }} enseignants
            </div>
            {{ $teachers->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
