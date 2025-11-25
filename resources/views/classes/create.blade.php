@extends('layouts.app')

@section('title', 'Créer une Classe')
@section('page-title', 'Créer une Classe')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Créer une Nouvelle Classe</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.classes.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom de la Classe *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ex: 6ème A, Terminale S" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="level" class="form-label">Niveau *</label>
                            <select name="level" id="level" required
                                class="form-select @error('level') is-invalid @enderror">
                                <option value="">Sélectionnez un niveau</option>
                                <option value="6ème" {{ old('level') == '6ème' ? 'selected' : '' }}>6ème</option>
                                <option value="5ème" {{ old('level') == '5ème' ? 'selected' : '' }}>5ème</option>
                                <option value="4ème" {{ old('level') == '4ème' ? 'selected' : '' }}>4ème</option>
                                <option value="3ème" {{ old('level') == '3ème' ? 'selected' : '' }}>3ème</option>
                                <option value="2nde" {{ old('level') == '2nde' ? 'selected' : '' }}>2nde</option>
                                <option value="1ère" {{ old('level') == '1ère' ? 'selected' : '' }}>1ère</option>
                                <option value="Terminale" {{ old('level') == 'Terminale' ? 'selected' : '' }}>Terminale</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="academic_year" class="form-label">Année scolaire *</label>
                            <select name="academic_year" id="academic_year" required
                                class="form-select @error('academic_year') is-invalid @enderror">
                                @foreach ($schoolYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ old('academic_year') == (string) $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="capacity" class="form-label">Capacité maximale</label>
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 40) }}"
                                class="form-control @error('capacity') is-invalid @enderror"
                                min="10" max="60">
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="teacher_id" class="form-label">Enseignant titulaire</label>
                            <select name="teacher_id" id="teacher_id"
                                class="form-select @error('teacher_id') is-invalid @enderror">
                                <option value="">Aucun enseignant titulaire</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} ({{ $teacher->matricule }})
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Description de la classe...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="is_active" class="form-check-label">Classe active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Créer la Classe
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb me-2"></i>À savoir</h6>
                    <ul class="mb-0 ps-3">
                        <li>Le nom de la classe doit être unique pour chaque niveau</li>
                        <li>L'enseignant titulaire peut être assigné plus tard</li>
                        <li>La capacité maximale peut être ajustée ultérieurement</li>
                        <li>Les champs marqués d'un * sont obligatoires</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6>Niveaux disponibles:</h6>
                    <ul class="list-unstyled small">
                        <li><strong>Collège:</strong> 6ème, 5ème, 4ème, 3ème</li>
                        <li><strong>Lycée:</strong> 2nde, 1ère, Terminale</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
