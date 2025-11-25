@extends('layouts.app')

@section('title', 'Gestion des Notes')
@section('page-title', 'Gestion des Notes')

@section('breadcrumbs')
<li class="breadcrumb-item active">Notes</li>
@endsection

@section('page-actions')
@can('create-marks')
<a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i>Saisir des notes
</a>
@endcan
@endsection

@section('content')
<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.marks.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="class_id" class="form-label">Classe</label>
                <select name="class_id" id="class_id" class="form-select">
                    <option value="">Toutes les classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="subject_id" class="form-label">Matière</label>
                <select name="subject_id" id="subject_id" class="form-select">
                    <option value="">Toutes les matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="trimester" class="form-label">Trimestre</label>
                <select name="trimester" id="trimester" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" {{ request('trimester') == 1 ? 'selected' : '' }}>1er Trimestre</option>
                    <option value="2" {{ request('trimester') == 2 ? 'selected' : '' }}>2ème Trimestre</option>
                    <option value="3" {{ request('trimester') == 3 ? 'selected' : '' }}>3ème Trimestre</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Actions</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.marks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Notes Saisies
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_marks'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-text fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Moyenne Générale
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['average_mark'] ?? 0, 2) }}/20</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Meilleure Note
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['max_mark'] ?? 0, 2) }}/20</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-trophy fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Plus Basse Note
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['min_mark'] ?? 0, 2) }}/20</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-graph-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des notes -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>Liste des notes
            <span class="badge bg-primary ms-2">{{ $marks->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $marks->currentPage() }} sur {{ $marks->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($marks->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Élève</th>
                        <th>Matière</th>
                        <th>Évaluation</th>
                        <th>Note</th>
                        <th>Appréciation</th>
                        <th>Date</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marks as $mark)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <span class="text-white small fw-bold">
                                        {{ substr($mark->student->first_name, 0, 1) }}{{ substr($mark->student->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $mark->student->first_name }} {{ $mark->student->last_name }}</div>
                                    <small class="text-muted">{{ $mark->student->class->name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $mark->evaluation->subject->name ?? 'N/A' }}</div>
                            <small class="text-muted">Coeff. {{ $mark->evaluation->subject->coefficient ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div>{{ $mark->evaluation->type ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $mark->evaluation->sequence_type ?? 'N/A' }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ number_format($mark->marks, 2) }}/{{ $mark->evaluation->max_marks ?? 20 }}</div>
                            @php
                                $percentage = ($mark->marks / ($mark->evaluation->max_marks ?? 20)) * 20;
                            @endphp
                            <small class="text-muted">{{ number_format($percentage, 1) }}/20</small>
                        </td>
                        <td>
                            @if($mark->appreciation)
                                <span class="badge
                                    {{ $mark->appreciation == 'Excellent' ? 'bg-success' : '' }}
                                    {{ $mark->appreciation == 'Très bien' ? 'bg-primary' : '' }}
                                    {{ $mark->appreciation == 'Bien' ? 'bg-info' : '' }}
                                    {{ $mark->appreciation == 'Assez bien' ? 'bg-warning' : '' }}
                                    {{ $mark->appreciation == 'Passable' ? 'bg-orange' : '' }}
                                    {{ $mark->appreciation == 'Insuffisant' ? 'bg-danger' : '' }}">
                                    {{ $mark->appreciation }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $mark->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('edit-marks')
                                <a href="{{ route('admin.marks.edit', $mark) }}"
                                   class="btn btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                                @can('delete-marks')
                                <form action="{{ route('admin.marks.destroy', $mark) }}" method="POST"
                                      class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?')">
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
            <h4 class="text-muted mt-3">Aucune note trouvée</h4>
            <p class="text-muted">Aucune note ne correspond à vos critères de recherche.</p>
            @can('create-marks')
            <a href="{{ route('admin.evaluations.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Saisir des notes
            </a>
            @endcan
        </div>
        @endif
    </div>

    @if($marks->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $marks->firstItem() }} à {{ $marks->lastItem() }} sur {{ $marks->total() }} notes
            </div>
            {{ $marks->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function confirmAction(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir effectuer cette action ?');
}
</script>
@endpush
