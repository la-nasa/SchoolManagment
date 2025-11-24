@extends('layouts.app')

@section('title', 'Gestion des Élèves')
@section('page-title', 'Gestion des Élèves')

@section('breadcrumbs')
<li class="breadcrumb-item active">Élèves</li>
@endsection

@section('page-actions')
@can('create-students')
<a href="{{ route('admin.students.create') }}" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i>Nouvel élève
</a>
@endcan
@endsection

@section('content')
<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.students.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Nom, prénom ou matricule...">
            </div>
            <div class="col-md-3">
                <label for="class_id" class="form-label">Classe</label>
                <select class="form-select" id="class_id" name="class_id">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des élèves -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-people me-2"></i>Liste des élèves
            <span class="badge bg-primary ms-2">{{ $students->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $students->currentPage() }} sur {{ $students->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($students->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Matricule</th>
                        <th>Nom et Prénom</th>
                        <th>Classe</th>
                        <th>Date de naissance</th>
                        <th>Genre</th>
                        <th>Statut</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>
                            <span class="fw-bold text-primary">{{ $student->matricule }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}"
                                     class="rounded-circle me-2" width="32" height="32">
                                <div>
                                    <div class="fw-semibold">{{ $student->full_name }}</div>
                                    @if($student->birth_place)
                                    <small class="text-muted">{{ $student->birth_place }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $student->class->full_name }}</span>
                        </td>
                        <td>
                            {{ $student->birth_date->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $student->age }} ans</small>
                        </td>
                        <td>
                            @if($student->gender == 'M')
                            <span class="badge bg-info">Masculin</span>
                            @else
                            <span class="badge bg-pink">Féminin</span>
                            @endif
                        </td>
                        <td>
                            @if($student->is_active)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('students.show', $student) }}"
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-students')
                                <a href="{{ route('admin.students.edit', $student) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete-students')
                                <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')">
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
            <i class="bi bi-people display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucun élève trouvé</h4>
            <p class="text-muted">Aucun élève ne correspond à vos critères de recherche.</p>
            @can('create-students')
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Ajouter le premier élève
            </a>
            @endcan
        </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($students->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $students->firstItem() }} à {{ $students->lastItem() }} sur {{ $students->total() }} élèves
            </div>
            {{ $students->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Auto-submit form on select change
document.getElementById('class_id').addEventListener('change', function() {
    this.form.submit();
});

// Initialize Select2
$(document).ready(function() {
    $('#class_id').select2({
        placeholder: 'Sélectionnez une classe',
        allowClear: true
    });
});
</script>

<style>
.bg-pink {
    background-color: #e83e8c !important;
}
</style>
@endpush
