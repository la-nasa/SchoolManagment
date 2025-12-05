@extends('layouts.app')

@section('title', 'Générer Bulletin - ' . $student->full_name)
@section('page-title', 'Générer Bulletin')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Élèves</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.students.show', $student) }}">{{ $student->full_name }}</a></li>
<li class="breadcrumb-item active">Générer Bulletin</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Générer Bulletin - {{ $student->full_name }}</h5>
    </div>
    <div class="card-body">
        @if (!$student->hasClass())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Attention :</strong> Cet élève n'est pas assigné à une classe. 
            <a href="{{ route('admin.students.edit', $student) }}" class="alert-link">
                Assignez une classe d'abord.
            </a>
        </div>
        @endif

        <form action="{{ route('admin.students.generate-bulletin', $student) }}" method="POST">
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
                                {{ ($currentTerm && $currentTerm->id == $term->id) ? 'selected' : '' }}>
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
                                {{ ($currentSchoolYear && $currentSchoolYear->id == $year->id) ? 'selected' : '' }}>
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
                        <i class="bi bi-star me-1"></i> Bulletin APC (Par Compétences)
                    </label>
                </div>
            </div>

            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Informations sur l'élève :</strong>
                <ul class="mb-0 mt-2">
                    <li>Nom complet : {{ $student->full_name }}</li>
                    <li>Matricule : {{ $student->matricule }}</li>
                    <li>Classe : {{ $student->getClassName() }}</li>
                    <li>Statut : {{ $student->is_active ? 'Actif' : 'Inactif' }}</li>
                </ul>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Retour
                </a>
                <button type="submit" class="btn btn-success" {{ !$student->hasClass() ? 'disabled' : '' }}>
                    <i class="bi bi-file-earmark-pdf me-1"></i> Générer Bulletin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection