@extends('layouts.app')

@section('title', $classe->name)
@section('page-title', $classe->name)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
    <li class="breadcrumb-item active">{{ $classe->name }}</li>
@endsection

@section('page-actions')
    <div class="btn-group">
        @can('edit', $classe)
            <a href="{{ route('admin.classes.edit', $classe) }}" class="btn btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
        @endcan

        <!-- Bouton pour générer les rapports -->
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateReportsModal">
            <i class="bi bi-file-earmark-pdf me-1"></i>Rapports
        </button>

        @can('delete', $classe)
            <form action="{{ route('admin.classes.destroy', $classe) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Informations de la classe -->
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Informations Générales
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
                            <p class="mb-0 fs-6">{{ $classe->level ?? 'Non spécifié' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Année Scolaire</label>
                            <p class="mb-0 fs-6">{{ $classe->schoolYear->year ?? 'Non spécifiée' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Enseignant Titulaire</label>
                            <p class="mb-0 fs-6">
                                {{ $classe->teacher->name ?? 'Aucun titulaire assigné' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Capacité</label>
                            <p class="mb-0 fs-6">{{ $classe->capacity }} élèves</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Élèves inscrits</label>
                            <p class="mb-0 fs-6">
                                <span class="fw-bold text-primary">{{ $classe->students->count() ?? 0 }}</span> élèves
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Statut</label>
                            <div>
                                @if ($classe->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-muted">Date de création</label>
                            <p class="mb-0 fs-6">
                                {{ $classe->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                        @if ($classe->description)
                            <div class="col-12 mt-2">
                                <label class="form-label fw-semibold text-muted">Description</label>
                                <p class="mb-0 fs-6">{{ $classe->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistiques détaillées -->
            <div class="card mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>Statistiques du Trimestre
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($classStats) && !empty($classStats))
                        <div class="row">
                            <div class="col-md-3 col-6 mb-3 text-center">
                                <div class="display-6 fw-bold text-primary mb-1">
                                    {{ number_format($classStats['class_average'] ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Moyenne générale</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3 text-center">
                                <div class="display-6 fw-bold text-success mb-1">
                                    {{ number_format($classStats['max_average'] ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Meilleure moyenne</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3 text-center">
                                <div class="display-6 fw-bold text-warning mb-1">
                                    {{ number_format($classStats['min_average'] ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Plus basse moyenne</small>
                            </div>
                            <div class="col-md-3 col-6 mb-3 text-center">
                                <div class="display-6 fw-bold text-info mb-1">
                                    {{ number_format($classStats['success_rate'] ?? 0, 1) }}%
                                </div>
                                <small class="text-muted">Taux de réussite</small>
                            </div>
                        </div>

                        <!-- Indicateur de performance -->
                        @if(isset($classStats['class_average']))
                            @php
                                $average = $classStats['class_average'];
                                $progressClass = $average >= 10 ? 'bg-success' : ($average >= 8 ? 'bg-warning' : 'bg-danger');
                                $percentage = min(100, ($average / 20) * 100);
                            @endphp
                            <div class="mt-4">
                                <label class="form-label small text-muted">Performance de la classe</label>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">0</span>
                                    <span class="small">Moyenne: {{ number_format($average, 2) }}/20</span>
                                    <span class="small">20</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $progressClass }}"
                                         role="progressbar"
                                         style="width: {{ $percentage }}%"
                                         aria-valuenow="{{ $percentage }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-graph-up text-muted display-4"></i>
                            <p class="text-muted mt-3">Aucune statistique disponible pour le moment.</p>
                            <p class="small text-muted">Les statistiques apparaîtront après la saisie des notes.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Liste des élèves -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-people me-2"></i>Élèves de la Classe
                        <span class="badge bg-primary ms-2">{{ $classe->students->count() ?? 0 }}</span>
                    </h6>
                    @can('create-students')
                    <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                       class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus me-1"></i>Ajouter
                    </a>
                    @endcan
                </div>
                <div class="card-body p-0">
                    @if ($classe->students && $classe->students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Élève</th>
                                        <th>Date de naissance</th>
                                        <th>Matricule</th>
                                        <th class="text-end">Moyenne générale</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($classe->students as $student)
                                        @php
                                            // Récupérer la moyenne générale de l'étudiant
                                            $studentAverage = null;
                                            if (isset($currentTerm) && isset($currentSchoolYear)) {
                                                $generalAverage = \App\Models\GeneralAverage::where([
                                                    'student_id' => $student->id,
                                                    'classe_id' => $classe->id,
                                                    'term_id' => $currentTerm->id,
                                                    'school_year_id' => $currentSchoolYear->id
                                                ])->first();
                                                $studentAverage = $generalAverage ? $generalAverage->average : null;
                                            }
                                        @endphp
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
                                                            <a href="{{ route('admin.students.show', $student) }}" class="text-decoration-none">
                                                                {{ $student->first_name }} {{ $student->last_name }}
                                                            </a>
                                                        </div>
                                                        <small class="text-muted">{{ $student->gender == 'M' ? 'Masculin' : 'Féminin' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($student->birth_date)
                                                    <div class="fs-6">{{ $student->birth_date->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($student->birth_date)->age }} ans</small>
                                                @else
                                                    <span class="text-muted fst-italic">Non renseignée</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $student->matricule ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if($studentAverage !== null)
                                                    <span class="h5 {{ $studentAverage >= 10 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($studentAverage, 2) }}/20
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">Non calculée</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.students.show', $student) }}"
                                                       class="btn btn-outline-primary"
                                                       title="Voir">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-success"
                                                            onclick="generateStudentBulletin({{ $student->id }})"
                                                            title="Générer bulletin">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </button>
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
                            @can('create-students')
                            <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                                class="btn btn-primary">
                                <i class="bi bi-person-plus me-1"></i>Ajouter un élève
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistiques résumées -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistiques Résumées</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h2 text-primary mb-1">{{ $classe->students->count() ?? 0 }}</div>
                            <small class="text-muted">Élèves inscrits</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h2 text-success mb-1">
                                {{ isset($classStats['total_students']) ? $classStats['total_students'] : '0' }}
                            </div>
                            <small class="text-muted">Avec notes</small>
                        </div>
                        <div class="col-6">
                            <div class="h2 text-info mb-1">
                                {{ isset($classStats['success_count']) ? $classStats['success_count'] : '0' }}
                            </div>
                            <small class="text-muted">Admis (≥10)</small>
                        </div>
                        <div class="col-6">
                            <div class="h2 text-warning mb-1">
                                {{ isset($classStats['failure_count']) ? $classStats['failure_count'] : '0' }}
                            </div>
                            <small class="text-muted">Non admis</small>
                        </div>
                    </div>

                    <!-- Indicateur de remplissage -->
                    @if($classe->capacity > 0)
                    <div class="mt-4">
                        <label class="form-label small text-muted">Taux de remplissage</label>
                        <div class="progress" style="height: 8px;">
                            @php
                                $fillPercentage = min(100, ($classe->students->count() / $classe->capacity) * 100);
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
                            {{ $classe->students->count() }}/{{ $classe->capacity }} ({{ number_format($fillPercentage, 1) }}%)
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Enseignants assignés -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Enseignants Assignés</h6>
                </div>
                <div class="card-body">
                    @if ($classe->teacherAssignments && $classe->teacherAssignments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($classe->teacherAssignments as $assignment)
                                <div class="list-group-item px-0 border-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-2"
                                            style="width: 32px; height: 32px;">
                                            <span class="text-white small fw-bold">
                                                {{ substr($assignment->teacher->name ?? 'T', 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">{{ $assignment->teacher->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $assignment->subject->name ?? 'Matière non spécifiée' }}</small>
                                            @if($assignment->is_titular)
                                                <span class="badge bg-warning ms-1">Titulaire</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-person-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">Aucun enseignant assigné</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Génération de rapports -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Génération de Rapports</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.bulletins.generate-class', $classe) }}" method="GET" class="mb-3">
                        <div class="mb-3">
                            <label for="term_id_pv" class="form-label">Trimestre</label>
                            <select name="term_id" id="term_id_pv" class="form-control" required>
                                <option value="">Sélectionnez un trimestre</option>
                                @foreach($terms as $termOption)
                                    <option value="{{ $termOption->id }}"
                                        {{ ($currentTerm && $termOption->id == $currentTerm->id) ? 'selected' : '' }}>
                                        {{ $termOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="school_year_id_pv" class="form-label">Année Scolaire</label>
                            <select name="school_year_id" id="school_year_id_pv" class="form-control" required>
                                <option value="">Sélectionnez une année</option>
                                @foreach($schoolYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ ($currentSchoolYear && $year->id == $currentSchoolYear->id) ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observations" class="form-label">Observations (optionnel)</label>
                            <textarea name="observations" id="observations" class="form-control"
                                      rows="2" placeholder="Observations générales..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-file-pdf me-1"></i> Générer le Procès-Verbal
                        </button>
                    </form>

                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Le procès-verbal inclut tous les élèves, matières, notes et statistiques.
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('create-students')
                        <a href="{{ route('admin.students.create', ['class_id' => $classe->id]) }}"
                            class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i>Ajouter un élève
                        </a>
                        @endcan

                        @can('create-evaluations')
                        <a href="{{ route('admin.evaluations.create', ['class_id' => $classe->id]) }}"
                            class="btn btn-outline-primary">
                            <i class="bi bi-clipboard-check me-1"></i>Nouvelle évaluation
                        </a>
                        @endcan

                        <button type="button" class="btn btn-outline-success"
                                data-bs-toggle="modal" data-bs-target="#generateReportsModal">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Générer bulletins
                        </button>

                        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Retour aux classes
                        </a>
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
                    <h5 class="modal-title">Générer Bulletins & PV - {{ $classe->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="generateReportsForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_term_id" class="form-label">
                                    <strong>Trimestre <span class="text-danger">*</span></strong>
                                </label>
                                <select id="modal_term_id" name="term_id" class="form-select" required>
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
                                <label for="modal_school_year_id" class="form-label">
                                    <strong>Année Scolaire <span class="text-danger">*</span></strong>
                                </label>
                                <select id="modal_school_year_id" name="school_year_id" class="form-select" required>
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
                                    <i class="bi bi-file-text me-1"></i> Bulletin Standard (Notes classiques)
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
                            <strong>Informations importantes :</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                <li>Les moyennes seront recalculées automatiquement</li>
                                <li>Un fichier ZIP contenant tous les bulletins sera téléchargé</li>
                                <li>Vérifiez que tous les élèves ont des notes saisies</li>
                                <li>Le PV récapitulatif liste tous les élèves avec leurs moyennes</li>
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
                    <button type="button" class="btn btn-warning" onclick="generateClassPV()">
                        <i class="bi bi-list-check me-1"></i> Générer PV
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Générer les bulletins pour toute la classe
function generateBulletins() {
    const termId = document.getElementById('modal_term_id').value;
    const schoolYearId = document.getElementById('modal_school_year_id').value;
    const type = document.querySelector('input[name="type"]:checked').value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Génération en cours...';
    button.disabled = true;

    // Créer un formulaire dynamique
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.bulletins.generate-class", $classe) }}';

    // Ajouter les champs
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

    // Soumettre le formulaire
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.blob();
    })
    .then(blob => {
        // Créer un lien de téléchargement
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Bulletins_{{ $classe->name }}_${termId}_${schoolYearId}.zip`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        document.body.removeChild(form);

        // Réactiver le bouton
        button.innerHTML = originalText;
        button.disabled = false;

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportsModal'));
        if (modal) {
            modal.hide();
        }

        // Afficher un message de succès
        showNotification('success', 'Bulletins générés avec succès ! Le téléchargement a commencé.');
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification('error', 'Erreur lors de la génération des bulletins. Veuillez réessayer.');
    });
}

// Générer le bulletin d'un étudiant spécifique
function generateStudentBulletin(studentId) {
    if (!confirm('Voulez-vous générer le bulletin de cet élève ?')) {
        return;
    }

    // Demander le trimestre et l'année scolaire
    const termId = prompt('Entrez l\'ID du trimestre (1, 2 ou 3):', '{{ $currentTerm->id ?? 1 }}');
    const schoolYearId = prompt('Entrez l\'ID de l\'année scolaire:', '{{ $currentSchoolYear->id ?? 1 }}');

    if (!termId || !schoolYearId) {
        alert('Veuillez spécifier le trimestre et l\'année scolaire');
        return;
    }

    // Créer un formulaire dynamique
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/students/${studentId}/generate-bulletin`;

    const fields = {
        '_token': '{{ csrf_token() }}',
        'term_id': termId,
        'school_year_id': schoolYearId,
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
    form.submit();
    document.body.removeChild(form);
}

// Générer le PV de la classe
function generateClassPV() {
    const termId = document.getElementById('modal_term_id').value;
    const schoolYearId = document.getElementById('modal_school_year_id').value;

    if (!termId || !schoolYearId) {
        alert('Veuillez sélectionner un trimestre et une année scolaire');
        return;
    }

    // Afficher un indicateur de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Génération en cours...';
    button.disabled = true;

    // Créer un formulaire dynamique
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route("admin.bulletins.generate-class", $classe) }}';

    // Ajouter les champs
    const fields = {
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

    // Soumettre le formulaire
    fetch(form.action + '?' + new URLSearchParams(fields))
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.blob();
    })
    .then(blob => {
        // Créer un lien de téléchargement
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `PV_{{ $classe->name }}_${termId}_${schoolYearId}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        document.body.removeChild(form);

        // Réactiver le bouton
        button.innerHTML = originalText;
        button.disabled = false;

        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportsModal'));
        if (modal) {
            modal.hide();
        }

        // Afficher un message de succès
        showNotification('success', 'PV généré avec succès ! Le téléchargement a commencé.');
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification('error', 'Erreur lors de la génération du PV. Veuillez réessayer.');
    });
}

// Fonction pour afficher des notifications
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    notification.innerHTML = `
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
    // Synchroniser les sélecteurs de trimestre
    const termSelect = document.getElementById('term_id_pv');
    const modalTermSelect = document.getElementById('modal_term_id');

    if (termSelect && modalTermSelect) {
        modalTermSelect.addEventListener('change', function() {
            termSelect.value = this.value;
        });
    }

    // Synchroniser les sélecteurs d'année scolaire
    const yearSelect = document.getElementById('school_year_id_pv');
    const modalYearSelect = document.getElementById('modal_school_year_id');

    if (yearSelect && modalYearSelect) {
        modalYearSelect.addEventListener('change', function() {
            yearSelect.value = this.value;
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.progress {
    background-color: #e9ecef;
}
.list-group-item {
    border: none;
    padding-left: 0;
    padding-right: 0;
}
.bg-primary, .bg-info {
    opacity: 0.9;
}
.display-6 {
    font-size: 2.5rem;
    font-weight: bold;
}
.badge.bg-secondary {
    font-size: 0.75em;
}
</style>
@endpush
