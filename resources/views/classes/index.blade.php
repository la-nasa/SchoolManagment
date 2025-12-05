@extends('layouts.app')

@section('title', 'Gestion des Classes')
@section('page-title', 'Gestion des Classes')

@section('breadcrumbs')
<li class="breadcrumb-item active">Classes</li>
@endsection

@section('page-actions')
@can('create-classes')
<a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Nouvelle classe
</a>
@endcan
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
                        <th width="150">Actions</th>
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
                                <span class="text-muted">{{ $class->teacher->name ?? $class->teacher->first_name . ' ' . $class->teacher->last_name }}</span>
                            @else
                                <span class="text-muted fst-italic">Non assigné</span>
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
                                   class="btn btn-outline-primary" title="Voir détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('edit-classes')
                                <a href="{{ route('admin.classes.edit', $class) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan

                                <!-- Bouton Rapports -->
                                <button type="button" class="btn btn-outline-success"
                                        data-bs-toggle="modal" data-bs-target="#reportsModal{{ $class->id }}"
                                        title="Générer rapports">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>

                                @can('delete-classes')
                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')">
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
            <i class="bi bi-building display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucune classe trouvée</h4>
            <p class="text-muted">Aucune classe ne correspond à vos critères de recherche.</p>
            @can('create-classes')
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Créer la première classe
            </a>
            @endcan
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

<!-- Modals pour chaque classe (en dehors du tableau) -->
@foreach($classes as $class)
<!-- Modal Bulletins & PV pour chaque classe -->
<div class="modal fade" id="reportsModal{{ $class->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Générer Rapports - {{ $class->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulletinForm{{ $class->id }}">
                    @csrf

                    <div class="mb-3">
                        <label for="term_id{{ $class->id }}" class="form-label">
                            <strong>Trimestre <span class="text-danger">*</span></strong>
                        </label>
                        <select id="term_id{{ $class->id }}" name="term_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ ($currentTerm->id ?? null) == $term->id ? 'selected' : '' }}>
                                    {{ $term->name }}
                                    @if($term->start_date && $term->end_date)
                                        ({{ $term->start_date->format('d/m/Y') }} - {{ $term->end_date->format('d/m/Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="school_year_id{{ $class->id }}" class="form-label">
                            <strong>Année Scolaire <span class="text-danger">*</span></strong>
                        </label>
                        <select id="school_year_id{{ $class->id }}" name="school_year_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($schoolYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ ($currentSchoolYear->id ?? null) == $year->id ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Type de Bulletin</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type"
                                   id="standardBulletin{{ $class->id }}" value="standard" checked>
                            <label class="form-check-label" for="standardBulletin{{ $class->id }}">
                                Bulletin Standard
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type"
                                   id="apcBulletin{{ $class->id }}" value="apc">
                            <label class="form-check-label" for="apcBulletin{{ $class->id }}">
                                Bulletin APC (Par Compétences)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i>
                        <strong>Important:</strong> Les moyennes seront recalculées automatiquement avant génération.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Fermer
                </button>
                <button type="button" class="btn btn-primary"
                        onclick="submitBulletinForm('{{ $class->id }}')">
                    <i class="bi bi-file-earmark-pdf"></i> Générer Bulletins
                </button>
                <button type="button" class="btn btn-warning"
                        onclick="submitPVForm('{{ $class->id }}')">
                    <i class="bi bi-list-check"></i> Générer PV
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
function submitBulletinForm(classeId) {
    const form = document.getElementById(`bulletinForm${classeId}`);
    const termId = form.querySelector(`#term_id${classeId}`).value;
    const schoolYearId = form.querySelector(`#school_year_id${classeId}`).value;
    const bulletinType = form.querySelector(`input[name="type"]:checked`).value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    // Afficher un indicateur de chargement
    const submitBtn = form.querySelector('.btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Génération...';
    submitBtn.disabled = true;

    // Créer un formulaire temporaire pour soumettre
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("admin.bulletins.generate-class", ":classe") }}'.replace(':classe', classeId);

    const fields = {
        '_token': '{{ csrf_token() }}',
        'term_id': termId,
        'school_year_id': schoolYearId,
        'type': bulletinType
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }

    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);

    // Réactiver le bouton après un délai
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);

    // Fermer le modal après soumission
    const modal = bootstrap.Modal.getInstance(document.getElementById(`reportsModal${classeId}`));
    if (modal) {
        modal.hide();
    }
}

function submitPVForm(classeId) {
    const form = document.getElementById(`bulletinForm${classeId}`);
    const termId = form.querySelector(`#term_id${classeId}`).value;
    const schoolYearId = form.querySelector(`#school_year_id${classeId}`).value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    // Afficher un indicateur de chargement
    const submitBtn = form.querySelector('.btn-warning');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Génération...';
    submitBtn.disabled = true;

    // Créer un formulaire temporaire pour soumettre
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("admin.bulletins.generate-pv", ":classe") }}'.replace(':classe', classeId);

    const fields = {
        '_token': '{{ csrf_token() }}',
        'term_id': termId,
        'school_year_id': schoolYearId
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }

    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);

    // Réactiver le bouton après un délai
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 3000);

    // Fermer le modal après soumission
    const modal = bootstrap.Modal.getInstance(document.getElementById(`reportsModal${classeId}`));
    if (modal) {
        modal.hide();
    }
}

// Initialisation Select2 si utilisé
document.addEventListener('DOMContentLoaded', function() {
    // Si vous utilisez Select2 pour les selects
    if (typeof $.fn.select2 !== 'undefined') {
        $('#level').select2({
            placeholder: 'Sélectionnez un niveau',
            allowClear: true
        });

        // Initialiser les selects dans les modals
        @foreach($classes as $class)
        $('#term_id{{ $class->id }}, #school_year_id{{ $class->id }}').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true,
            dropdownParent: $('#reportsModal{{ $class->id }}')
        });
        @endforeach
    }
});
</script>
@endpush

@push('styles')
<style>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
}

.modal-header {
    border-bottom: 2px solid #198754;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

/* Style pour les modals */
.modal-content {
    border: none;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.modal-body {
    padding: 1.5rem;
}

.select2-container {
    width: 100% !important;
}
</style>
@endpush