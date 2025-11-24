@extends('layouts.app')

@section('title', 'Nouvel Élève')
@section('page-title', 'Nouvel Élève')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('students.index') }}">Élèves</a></li>
<li class="breadcrumb-item active">Nouvel élève</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Informations de l'élève</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Nom *</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Date de naissance *</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label">Genre *</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Sélectionnez...</option>
                                <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="birth_place" class="form-label">Lieu de naissance</label>
                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place') }}">
                            @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Classe *</label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->full_name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="school_year_id" class="form-label">Année scolaire *</label>
                            <select class="form-select @error('school_year_id') is-invalid @enderror" id="school_year_id" name="school_year_id" required>
                                @foreach($schoolYears as $schoolYear)
                                <option value="{{ $schoolYear->id }}" {{ $currentSchoolYear->id == $schoolYear->id ? 'selected' : '' }}>{{ $schoolYear->year }}</option>
                                @endforeach
                            </select>
                            @error('school_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="photo" class="form-label">Photo (optionnel)</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</div>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Créer l'élève
                        </button>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
                        <li>Le matricule sera généré automatiquement</li>
                        <li>L'élève sera actif par défaut</li>
                        <li>La photo est optionnelle</li>
                        <li>Les champs marqués d'un * sont obligatoires</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6>Année scolaire courante:</h6>
                    <p class="mb-1"><strong>{{ $currentSchoolYear->year }}</strong></p>
                    <small class="text-muted">Du {{ $currentSchoolYear->start_date->format('d/m/Y') }} au {{ $currentSchoolYear->end_date->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#class_id').select2({
        placeholder: 'Sélectionnez une classe',
        allowClear: false
    });
});
</script>
@endpush
