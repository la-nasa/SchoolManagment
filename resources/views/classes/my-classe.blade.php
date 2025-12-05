@extends('layouts.app')

@section('title', 'Ma Classe - ' . $classe->name)
@section('page-title', 'Ma Classe - ' . $classe->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('titular.dashboard') }}">Tableau de Bord</a></li>
<li class="breadcrumb-item active">Ma Classe</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateReportsModal">
        <i class="bi bi-file-earmark-pdf me-1"></i>Générer Rapports
    </button>
    <a href="{{ route('titular.evaluations.create', ['class_id' => $classe->id]) }}" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-1"></i>Nouvelle Évaluation
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Informations de la classe -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Informations de la Classe
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Nom de la Classe</label>
                        <p class="mb-0 fs-6">{{ $classe->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Niveau</label>
                        <p class="mb-0 fs-6">{{ $classe->level }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Année Scolaire</label>
                        <p class="mb-0 fs-6">{{ $classe->schoolYear->year ?? 'Non spécifiée' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Capacité</label>
                        <p class="mb-0 fs-6">{{ $classe->capacity }} élèves</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Élèves inscrits</label>
                        <p class="mb-0 fs-6">
                            <span class="fw-bold text-primary">{{ $classe->students_count ?? 0 }}</span> élèves
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-muted">Moyenne de classe</label>
                        <p class="mb-0 fs-6 fw-bold text-success">
                            {{ number_format($classStats['average'] ?? 0, 2) }}/20
                        </p>
                    </div>
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
                <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}" 
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Ajouter
                </a>
            </div>
            <div class="card-body p-0">
                @if ($classe->students && $classe->students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Élève</th>
                                    <th>Matricule</th>
                                    <th>Date de naissance</th>
                                    <th class="text-end">Moyenne générale</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classe->students as $student)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                    style="width: 32px; height: 32px;">
                                                    <span class="text-white small fw-bold">
                                                        {{ substr($student->first_name ?? 'E', 0, 1) }}{{ substr($student->last_name ?? 'L', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $student->first_name }} {{ $student->last_name }}
                                                    </div>
                                                    <small class="text-muted">{{ $student->parent_phone ?? 'Tél. non renseigné' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $student->matricule ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if ($student->date_of_birth)
                                                <div class="fs-6">{{ $student->date_of_birth->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $student->date_of_birth->age }} ans</small>
                                            @else
                                                <span class="text-muted fst-italic">Non renseignée</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold 
                                                {{ ($student->average_mark ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($student->average_mark ?? 0, 2) }}/20
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.students.show', $student) }}" 
                                                   class="btn btn-outline-primary" title="Voir profil">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.reports.bulletin', $student) }}" 
                                                   class="btn btn-outline-success" title="Générer bulletin">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
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
                        <h4 class="text-muted mt-3">Aucun élève</h4>
                        <p class="text-muted">Aucun élève n'est inscrit dans cette classe.</p>
                        <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                            class="btn btn-primary">
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
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistiques de la Classe</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="h4 text-primary mb-1">{{ $classe->students_count ?? 0 }}</div>
                        <small class="text-muted">Élèves inscrits</small>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="h4 text-success mb-1">{{ number_format($classStats['average'] ?? 0, 2) }}</div>
                        <small class="text-muted">Moyenne classe</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-info mb-1">{{ number_format($classStats['min'] ?? 0, 2) }}</div>
                        <small class="text-muted">Note minimum</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning mb-1">{{ number_format($classStats['max'] ?? 0, 2) }}</div>
                        <small class="text-muted">Note maximum</small>
                    </div>
                </div>

                <!-- Indicateur de remplissage -->
                @if($classe->capacity > 0)
                <div class="mt-3">
                    <label class="form-label small text-muted">Taux de remplissage</label>
                    <div class="progress" style="height: 8px;">
                        @php
                            $fillPercentage = min(100, ($classe->students_count / $classe->capacity) * 100);
                            $progressClass = $fillPercentage >= 90 ? 'bg-danger' : ($fillPercentage >= 75 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="progress-bar {{ $progressClass }}"
                             role="progressbar"
                             style="width: {{ $fillPercentage }}%"
                             aria-valuenow="{{ $fillPercentage }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="text-end small text-muted mt-1">
                        {{ $classe->students_count }}/{{ $classe->capacity }} ({{ number_format($fillPercentage, 1) }}%)
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Évaluations récentes -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Évaluations Récentes</h6>
            </div>
            <div class="card-body">
                @php
                    $recentEvaluations = $classe->evaluations()->latest()->take(5)->get();
                @endphp
                
                @if($recentEvaluations->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentEvaluations as $evaluation)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold small">{{ $evaluation->title }}</div>
                                        <small class="text-muted">{{ $evaluation->subject->name ?? 'Matière' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $evaluation->exam_date->format('d/m/Y') }}</small>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $evaluation->max_marks }} pts</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-clipboard-x text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2 mb-0">Aucune évaluation</p>
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('titular.evaluations') }}" class="btn btn-sm btn-outline-primary">
                        Voir toutes les évaluations
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                        class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Ajouter un élève
                    </a>
                    <a href="{{ route('titular.evaluations.create', ['class_id' => $classe->id]) }}"
                        class="btn btn-outline-primary">
                        <i class="bi bi-clipboard-check me-1"></i>Nouvelle évaluation
                    </a>
                    <button type="button" class="btn btn-outline-success"
                            data-bs-toggle="modal" data-bs-target="#generateReportsModal">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Générer rapports
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Générer Rapports -->
<div class="modal fade" id="generateReportsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Générer Rapports - {{ $classe->name }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateReportsForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="term_id" class="form-label">
                                <strong>Trimestre <span class="text-danger">*</span></strong>
                            </label>
                            <select id="term_id" name="term_id" class="form-select" required>
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
                        <div class="col-md-6">
                            <label for="school_year_id" class="form-label">
                                <strong>Année Scolaire <span class="text-danger">*</span></strong>
                            </label>
                            <select id="school_year_id" name="school_year_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($schoolYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ ($currentSchoolYear->id ?? null) == $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
                                <i class="bi bi-star me-1"></i> Bulletin APC
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Informations importantes :</strong>
                        <ul class="mb-0 mt-2 ps-3">
                            <li>Les moyennes seront recalculées automatiquement</li>
                            <li>Un fichier ZIP contenant tous les bulletins sera téléchargé</li>
                            <li>Vérifiez que tous les élèves ont des notes saisies</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary" onclick="generateBulletins()">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Générer Bulletins
                </button>
                <button type="button" class="btn btn-warning" onclick="generatePV()">
                    <i class="bi bi-list-check me-1"></i> Générer PV
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateBulletins() {
    const termId = document.getElementById('term_id').value;
    const schoolYearId = document.getElementById('school_year_id').value;
    const type = document.querySelector('input[name="type"]:checked').value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.bulletins.generate-class", $classe) }}';

    const fields = {
        '_token': '{{ csrf_token() }}',
        'term_id': termId,
        'school_year_id': schoolYearId,
        'type': type
    };

    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportsModal'));
    if (modal) {
        modal.hide();
    }
}

function generatePV() {
    const termId = document.getElementById('term_id').value;
    const schoolYearId = document.getElementById('school_year_id').value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.bulletins.generate-pv", $classe) }}';

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
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Fermer le modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportsModal'));
    if (modal) {
        modal.hide();
    }
}

// Initialisation Select2 si utilisé
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#term_id, #school_year_id').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true,
            dropdownParent: $('#generateReportsModal')
        });
    }
});
</script>
@endpush