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
    
    <!-- Bouton pour générer bulletin avec modal -->
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateBulletinModal">
        <i class="bi bi-file-text me-1"></i>Générer Bulletin
    </button>
    
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="position-relative d-inline-block">
                    @if($student->photo)
                        <img src="{{ asset('storage/' . $student->photo) }}" 
                             alt="{{ $student->full_name }}" 
                             class="rounded-circle mb-3" width="120" height="120"
                             style="object-fit: cover;">
                    @else
                        <div class="rounded-circle mb-3 d-inline-flex align-items-center justify-content-center bg-primary text-white" 
                             style="width: 120px; height: 120px; font-size: 2rem;">
                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h4 class="card-title">{{ $student->full_name }}</h4>
                <p class="text-muted mb-2">{{ $student->matricule ?? 'N/A' }}</p>
                <span class="badge bg-light text-dark fs-6">{{ $student->classe->full_name ?? 'Classe non assignée' }}</span>

                <div class="mt-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary mb-1">{{ $student->age ?? 'N/A' }}</div>
                            <small class="text-muted">Âge</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1">
                                @if($student->gender == 'M') M @elseif($student->gender == 'F') F @else N/A @endif
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
                        <td><strong>{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'Non renseignée' }}</strong></td>
                    </tr>
                    @if($student->birth_place)
                    <tr>
                        <td class="text-muted">Lieu de naissance:</td>
                        <td><strong>{{ $student->birth_place }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Classe:</td>
                        <td><strong>{{ $student->classe->full_name ?? 'Non assignée' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Année scolaire:</td>
                        <td><strong>{{ $student->schoolYear->year ?? 'Non assignée' }}</strong></td>
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
        <!-- Performance académique -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Performance académique</h6>
            </div>
            <div class="card-body">
                @if($student->generalAverages && $student->generalAverages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Trimestre</th>
                                <th>Année Scolaire</th>
                                <th>Moyenne Générale</th>
                                <th>Rang</th>
                                <th>Appréciation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->generalAverages as $average)
                            <tr>
                                <td><strong>{{ $average->term->name ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $average->schoolYear->year ?? 'N/A' }}</strong></td>
                                <td>
                                    <span class="h5 {{ ($average->average ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($average->average ?? 0, 2) }}/20
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $average->rank ?? 'N/A' }}/{{ $average->total_students ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $average->appreciation ?? 'Non noté' }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="generateSpecificBulletin({{ $student->id }}, {{ $average->term_id }}, {{ $average->school_year_id }})">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Bulletin
                                    </button>
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
                    <p class="text-muted small">Les moyennes apparaîtront après calcul.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Notes par matière -->
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Notes par matière</h6>
            </div>
            <div class="card-body">
                @if($student->averages && $student->averages->count() > 0)
                <div class="row">
                    @foreach($student->averages->groupBy('term_id') as $termAverages)
                    @php 
                        $term = $termAverages->first()->term ?? null;
                        $schoolYear = $termAverages->first()->schoolYear ?? null;
                    @endphp
                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="mb-0">{{ $term->name ?? 'Trimestre inconnu' }}</h6>
                            <small class="text-muted">{{ $schoolYear->year ?? '' }}</small>
                        </div>
                        <div class="row">
                            @foreach($termAverages as $average)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-truncate" title="{{ $average->subject->name ?? 'Matière inconnue' }}">
                                            {{ $average->subject->name ?? 'Matière inconnue' }}
                                        </h6>
                                        <div class="h4 {{ ($average->average ?? 0) >= 10 ? 'text-success' : 'text-danger' }} mb-2">
                                            {{ number_format($average->average ?? 0, 2) }}/20
                                        </div>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <small class="text-muted me-2">Coef: {{ $average->subject->coefficient ?? 1 }}</small>
                                            <span class="badge bg-light text-dark">{{ $average->appreciation ?? 'Non noté' }}</span>
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
                    <p class="text-muted small">Les notes apparaîtront après saisie et calcul des moyennes.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Dernières évaluations -->
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Dernières évaluations</h6>
            </div>
            <div class="card-body">
                @if($student->marks && $student->marks->count() > 0)
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
                                <td><small>{{ $mark->evaluation->exam_date->format('d/m/Y') ?? 'Date inconnue' }}</small></td>
                                <td>{{ $mark->subject->name ?? 'Matière inconnue' }}</td>
                                <td>
                                    <small>{{ $mark->evaluation->title ?? 'Titre inconnu' }}</small>
                                    <br><span class="badge bg-secondary">{{ $mark->evaluation->examType->name ?? 'Type inconnu' }}</span>
                                </td>
                                <td>
                                    @if($mark->is_absent)
                                    <span class="badge bg-warning">Absent</span>
                                    @else
                                    <span class="fw-bold {{ ($mark->marks ?? 0) >= (($mark->evaluation->max_marks ?? 20) / 2) ? 'text-success' : 'text-danger' }}">
                                        {{ $mark->marks ?? 0 }}/{{ $mark->evaluation->max_marks ?? 20 }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $mark->comment ?? 'Aucun commentaire' }}</small>
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
                    <p class="text-muted small">Les évaluations apparaîtront après saisie des notes.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal pour générer le bulletin -->
<div class="modal fade" id="generateBulletinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Générer Bulletin - {{ $student->full_name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulletinForm" action="{{ route('admin.students.generate-bulletin', $student) }}" method="GET">
                    @csrf

                    <div class="mb-3">
                        <label for="term_id" class="form-label">
                            <strong>Trimestre <span class="text-danger">*</span></strong>
                        </label>
                        <select id="term_id" name="term_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}"
                                    {{ ($currentTerm && $currentTerm->id == $term->id) ? 'selected' : '' }}>
                                    {{ $term->name }}
                                    @if($term->start_date && $term->end_date)
                                        ({{ $term->start_date->format('d/m/Y') }} - {{ $term->end_date->format('d/m/Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="school_year_id" class="form-label">
                            <strong>Année Scolaire <span class="text-danger">*</span></strong>
                        </label>
                        <select id="school_year_id" name="school_year_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($schoolYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ ($currentSchoolYear && $currentSchoolYear->id == $year->id) ? 'selected' : '' }}>
                                    {{ $year->year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Type de Bulletin</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type"
                                   id="standardBulletin" value="standard" checked>
                            <label class="form-check-label" for="standardBulletin">
                                <i class="bi bi-file-text me-1"></i> Bulletin Standard
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type"
                                   id="apcBulletin" value="apc">
                            <label class="form-check-label" for="apcBulletin">
                                <i class="bi bi-star me-1"></i> Bulletin APC (Par Compétences)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informations :</strong> Les moyennes seront recalculées automatiquement avant génération.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Fermer
                </button>
                <button type="submit" form="bulletinForm" class="btn btn-success">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Générer Bulletin
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateSpecificBulletin(studentId, termId, schoolYearId) {
    // Créer un formulaire dynamique
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = `/students/${studentId}/generate-bulletin`;
    
    // Ajouter les champs
    const fields = {
        'term_id': termId,
        'school_year_id': schoolYearId || '{{ $currentSchoolYear->id ?? 1 }}',
        'type': 'standard'
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    
    // Afficher un indicateur de chargement
    showLoading('Génération du bulletin en cours...');
    
    // Soumettre le formulaire
    fetch(form.action + '?' + new URLSearchParams(fields))
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors de la génération');
        }
        return response.blob();
    })
    .then(blob => {
        // Créer un lien de téléchargement
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `bulletin_${studentId}_${termId}_${schoolYearId}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        document.body.removeChild(form);
        
        hideLoading();
        showNotification('success', 'Bulletin généré avec succès !');
    })
    .catch(error => {
        console.error('Erreur:', error);
        hideLoading();
        showNotification('error', 'Erreur lors de la génération du bulletin.');
        document.body.removeChild(form);
    });
}

function showLoading(message = 'Chargement...') {
    // Créer un overlay de chargement
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        color: white;
        font-size: 1.2rem;
    `;
    loadingOverlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-light mb-3" role="status"></div>
            <div>${message}</div>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
    `;
    notification.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du modal si besoin
    const modalElement = document.getElementById('generateBulletinModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        
        // Réinitialiser le modal quand il est fermé
        modalElement.addEventListener('hidden.bs.modal', function () {
            document.getElementById('bulletinForm').reset();
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.avatar-placeholder {
    background: linear-gradient(45deg, #3498db, #2ecc71);
    color: white;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-title.text-truncate {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.badge.bg-light {
    border: 1px solid #dee2e6;
}

.table-borderless td {
    border: none !important;
}
</style>
@endpush