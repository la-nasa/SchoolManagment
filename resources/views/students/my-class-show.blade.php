@extends('layouts.app')

@section('title', 'Détails de l\'Élève - ' . $student->full_name)
@section('page-title', 'Détails de l\'Élève')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('titular.dashboard') }}">Tableau de Bord</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('titular.students') }}">Mes Élèves</a>
</li>
<li class="breadcrumb-item active">{{ $student->full_name }}</li>
@endsection

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('reports.bulletin', $student->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-file-text me-1"></i>Générer Bulletin
    </a>
    <a href="{{ route('titular.students') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="rounded-circle mb-3" width="120" height="120">
                <h4 class="card-title">{{ $student->full_name }}</h4>
                <p class="text-muted mb-2">{{ $student->matricule }}</p>

                <div class="mt-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary mb-1">{{ $student->birth_date->age }} ans</div>
                            <small class="text-muted">Âge</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success mb-1">
                                {{ $student->gender == 'M' ? 'Masculin' : 'Féminin' }}
                            </div>
                            <small class="text-muted">Genre</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Date de naissance:</td>
                        <td><strong>{{ $student->birth_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lieu de naissance:</td>
                        <td><strong>{{ $student->birth_place ?? 'Non renseigné' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Classe:</td>
                        <td><strong>{{ $student->class->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Année scolaire:</td>
                        <td><strong>{{ $student->schoolYear->year }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Résultats Scolaires</h6>
            </div>
            <div class="card-body">
                @if($student->generalAverages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Trimestre</th>
                                <th>Moyenne Générale</th>
                                <th>Rang</th>
                                <th>Appréciation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->generalAverages as $average)
                            <tr>
                                <td>{{ $average->term->name }}</td>
                                <td>
                                    <span class="badge {{ $average->average >= 10 ? 'bg-success' : 'bg-danger' }}">
                                        {{ number_format($average->average, 2) }}/20
                                    </span>
                                </td>
                                <td>{{ $average->rank }}/{{ $average->total_students }}</td>
                                <td>{{ $average->appreciation }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucun résultat scolaire disponible.</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Notes Récentes</h6>
            </div>
            <div class="card-body">
                @if($student->marks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Évaluation</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->marks->take(10) as $mark)
                            <tr>
                                <td>{{ $mark->evaluation->subject->name }}</td>
                                <td>{{ $mark->evaluation->title }}</td>
                                <td>
                                    <span class="badge {{ $mark->marks >= 10 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $mark->marks }}/{{ $mark->evaluation->max_marks }}
                                    </span>
                                </td>
                                <td>{{ $mark->evaluation->date->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune note disponible.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
