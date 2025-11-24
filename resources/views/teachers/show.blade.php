@extends('layouts.app')

@section('title', 'Détails de l\'Enseignant - ' . $teacher->name)
@section('page-title', 'Détails de l\'Enseignant')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Enseignants</a></li>
<li class="breadcrumb-item active">{{ $teacher->name }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    @can('edit-users')
    <a href="{{ route('admin.users.edit',  $teacher->id) }}" class="btn btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Modifier
    </a>
    @endcan
    @can('edit-users')
    <form action="{{ route('admin.users.reset-password', $teacher) }}" method="POST" class="d-inline" onsubmit="return confirm('Réinitialiser le mot de passe de cet enseignant ?')">
        @csrf
        <button type="submit" class="btn btn-outline-warning">
            <i class="bi bi-key me-1"></i>Réinitialiser MDP
        </button>
    </form>
    @endcan
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->name }}" class="rounded-circle mb-3" width="120" height="120">
                <h4 class="card-title">{{ $teacher->name }}</h4>
                <p class="text-muted mb-2">{{ $teacher->matricule }}</p>
                <span class="badge {{ $teacher->isTitularTeacher() ? 'bg-success' : 'bg-info' }} fs-6">
                    {{ $teacher->role_name }}
                </span>

                <div class="mt-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary mb-1">
                                @if($teacher->birth_date)
                                {{ $teacher->birth_date->age }}
                                @else
                                -
                                @endif
                            </div>
                            <small class="text-muted">Âge</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1">
                                @if($teacher->gender == 'M') M @elseif($teacher->gender == 'F') F @else - @endif
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
                        <td class="text-muted" width="40%">Email:</td>
                        <td><a href="mailto:{{ $teacher->email }}">{{ $teacher->email }}</a></td>
                    </tr>
                    @if($teacher->phone)
                    <tr>
                        <td class="text-muted">Téléphone:</td>
                        <td><a href="tel:{{ $teacher->phone }}">{{ $teacher->phone }}</a></td>
                    </tr>
                    @endif
                    @if($teacher->birth_date)
                    <tr>
                        <td class="text-muted">Date de naissance:</td>
                        <td><strong>{{ $teacher->birth_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    @endif
                    @if($teacher->class)
                    <tr>
                        <td class="text-muted">Classe titulaire:</td>
                        <td><strong>{{ $teacher->class->full_name }}</strong></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Statut:</td>
                        <td>
                            @if($teacher->is_active)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dernière connexion:</td>
                        <td>
                            @if($teacher->last_login_at)
                            <strong>{{ $teacher->last_login_at->format('d/m/Y H:i') }}</strong>
                            @else
                            <span class="text-muted">Jamais connecté</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Inscrit le:</td>
                        <td><strong>{{ $teacher->created_at->format('d/m/Y') }}</strong></td>
                    </tr>
                </table>

                @if($teacher->address)
                <div class="mt-3">
                    <h6>Adresse:</h6>
                    <p class="text-muted small">{{ $teacher->address }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Matières enseignées</h6>
            </div>
            <div class="card-body">
                @if($teacher->teacherAssignments->count() > 0)
                <div class="row">
                    @foreach($teacher->teacherAssignments->groupBy('class_id') as $classAssignments)
                    @php $class = $classAssignments->first()->class; @endphp
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2">
                            Classe: {{ $class->full_name }}
                            @if($teacher->class_id == $class->id)
                            <span class="badge bg-success ms-2">Titulaire</span>
                            @endif
                        </h6>
                        <div class="row">
                            @foreach($classAssignments as $assignment)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $assignment->subject->name }}</h6>
                                        <p class="card-text text-muted small">{{ $assignment->subject->code }}</p>
                                        <span class="badge bg-light text-dark">Coefficient: {{ $assignment->subject->coefficient }}</span>
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
                    <p class="text-muted mt-2">Aucune matière assignée.</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Statistiques d'activité</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-primary mb-2">
                                {{ $teacher->teacherAssignments->groupBy('class_id')->count() }}
                            </div>
                            <small class="text-muted">Classes assignées</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-success mb-2">
                                {{ $teacher->teacherAssignments->count() }}
                            </div>
                            <small class="text-muted">Matières enseignées</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-info mb-2">
                                @php
                                    $totalStudents = 0;
                                    foreach($teacher->teacherAssignments->groupBy('class_id') as $classAssignments) {
                                        $totalStudents += $classAssignments->first()->class->students->count();
                                    }
                                @endphp
                                {{ $totalStudents }}
                            </div>
                            <small class="text-muted">Élèves concernés</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <div class="h3 text-warning mb-2">
                                @php
                                    $evaluationsCount = \App\Models\Evaluation::whereIn('class_id', $teacher->teacherAssignments->pluck('class_id'))
                                        ->whereIn('subject_id', $teacher->teacherAssignments->pluck('subject_id'))
                                        ->count();
                                @endphp
                                {{ $evaluationsCount }}
                            </div>
                            <small class="text-muted">Évaluations créées</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($teacher->last_login_at)
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Activité récente</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Dernière connexion: <strong>{{ $teacher->last_login_at->format('d/m/Y à H:i') }}</strong>
                    <br>
                    <small class="text-muted">Soit {{ $teacher->last_login_at->diffForHumans() }}</small>
                </div>

                @if($teacher->isPasswordTemporary())
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Mot de passe temporaire</strong> - L'enseignant doit changer son mot de passe à la prochaine connexion.
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
