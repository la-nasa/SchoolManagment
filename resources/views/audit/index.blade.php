@extends('layouts.app')

@section('title', 'Journal d\'Audit')
@section('page-title', 'Journal d\'Audit')

@section('breadcrumbs')
<li class="breadcrumb-item active">Journal d'Audit</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('admin.audit.export') }}" class="btn btn-success">
        <i class="bi bi-download me-1"></i>Exporter
    </a>
    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filtersModal">
        <i class="bi bi-funnel me-1"></i>Filtres
    </button>
</div>
@endsection

@section('content')
<!-- Filtres Modal -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtrer le journal d'audit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.audit.index') }}" method="GET">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="event" class="form-label">Événement</label>
                            <select class="form-select" id="event" name="event">
                                <option value="">Tous les événements</option>
                                @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst($event) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Tous les utilisateurs</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->matricule }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_from" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="date_to" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Journal d'audit -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-shield-check me-2"></i>Journal des activités
            <span class="badge bg-primary ms-2">{{ $audits->total() }}</span>
        </h6>
        <div class="text-muted small">
            Page {{ $audits->currentPage() }} sur {{ $audits->lastPage() }}
        </div>
    </div>
    <div class="card-body p-0">
        @if($audits->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="120">Date</th>
                        <th width="100">Utilisateur</th>
                        <th width="100">Événement</th>
                        <th>Modèle</th>
                        <th>Détails</th>
                        <th width="80">IP</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audits as $audit)
                    <tr>
                        <td>
                            <small class="text-muted d-block">{{ $audit->created_at->format('d/m/Y') }}</small>
                            <small class="text-muted">{{ $audit->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if($audit->user)
                            <div class="d-flex align-items-center">
                                <img src="{{ $audit->user->photo_url }}" alt="{{ $audit->user->name }}"
                                     class="rounded-circle me-2" width="24" height="24">
                                <div>
                                    <div class="small fw-semibold">{{ $audit->user->name }}</div>
                                    <small class="text-muted">{{ $audit->user->role_name }}</small>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">Système</span>
                            @endif
                        </td>
                        <td>
                            @if($audit->event == 'created')
                            <span class="badge bg-success">Création</span>
                            @elseif($audit->event == 'updated')
                            <span class="badge bg-warning">Modification</span>
                            @elseif($audit->event == 'deleted')
                            <span class="badge bg-danger">Suppression</span>
                            @else
                            <span class="badge bg-info">{{ $audit->event }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ class_basename($audit->auditable_type) }}</span>
                            <br>
                            <small class="text-muted">ID: {{ $audit->auditable_id }}</small>
                        </td>
                        <td>
                            @php
                                 $oldValues = is_array($audit->old_values) ? $audit->old_values : 
                (is_string($audit->old_values) ? json_decode($audit->old_values, true) : []);
    
    $newValues = is_array($audit->new_values) ? $audit->new_values : 
                (is_string($audit->new_values) ? json_decode($audit->new_values, true) : []);
                            @endphp

                            @if($audit->event == 'created')
                            <small class="text-success">
                                <i class="bi bi-plus-circle me-1"></i>Nouvel enregistrement créé
                            </small>
                            @elseif($audit->event == 'updated')
                            <div class="small">
                                @foreach($newValues as $key => $value)
                                    @if(isset($oldValues[$key]) && $oldValues[$key] != $value)
                                    <div>
                                        <strong>{{ $key }}:</strong>
                                        <span class="text-danger">{{ $oldValues[$key] ?? 'null' }}</span> →
                                        <span class="text-success">{{ $value }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @elseif($audit->event == 'deleted')
                            <small class="text-danger">
                                <i class="bi bi-trash me-1"></i>Enregistrement supprimé
                            </small>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $audit->ip_address }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.audit.show', $audit) }}"
                               class="btn btn-sm btn-outline-primary" title="Détails">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-shield-check display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Aucun enregistrement d'audit</h4>
            <p class="text-muted">Aucune activité ne correspond à vos critères de recherche.</p>
        </div>
        @endif
    </div>

    @if($audits->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $audits->firstItem() }} à {{ $audits->lastItem() }} sur {{ $audits->total() }} enregistrements
            </div>
            {{ $audits->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#event, #user_id').select2({
        placeholder: 'Sélectionnez une option',
        allowClear: true
    });
});
</script>
@endpush
