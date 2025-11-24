@extends('layouts.app')

@section('title', 'Modifier l\'Élève - ' . $student->full_name)
@section('page-title', 'Modifier l\'Élève')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.students.index') }}">Élèves</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.students.show', $student) }}">{{ $student->full_name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Modifier les informations</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Nom *</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Date de naissance *</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date', $student->birth_date->format('Y-m-d')) }}" required>
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label">Genre *</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">Sélectionnez...</option>
                                <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="birth_place" class="form-label">Lieu de naissance</label>
                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror" id="birth_place" name="birth_place" value="{{ old('birth_place', $student->birth_place) }}">
                            @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Classe *</label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->full_name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="school_year_id" class="form-label">Année scolaire *</label>
                            <select class="form-select @error('school_year_id') is-invalid @enderror" id="school_year_id" name="school_year_id" required>
                                @foreach($schoolYears as $schoolYear)
                                <option value="{{ $schoolYear->id }}" {{ old('school_year_id', $student->school_year_id) == $schoolYear->id ? 'selected' : '' }}>{{ $schoolYear->year }}</option>
                                @endforeach
                            </select>
                            @error('school_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="photo" class="form-label">Photo</label>
                            @if($student->photo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="Photo actuelle" class="img-thumbnail" width="100">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo" value="1">
                                    <label class="form-check-label" for="remove_photo">Supprimer la photo actuelle</label>
                                </div>
                            </div>
                            @endif
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</div>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $student->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Élève actif</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Mettre à jour
                        </button>
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Informations actuelles</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $student->photo_url }}" alt="{{ $student->full_name }}" class="rounded-circle" width="80" height="80">
                    <h6 class="mt-2">{{ $student->full_name }}</h6>
                    <p class="text-muted mb-1">{{ $student->matricule }}</p>
                    <span class="badge bg-light text-dark">{{ $student->class->full_name }}</span>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Date de naissance:</td>
                        <td><strong>{{ $student->birth_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Âge:</td>
                        <td><strong>{{ $student->age }} ans</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Genre:</td>
                        <td><strong>@if($student->gender == 'M') Masculin @else Féminin @endif</strong></td>
                    </tr>
                    @if($student->birth_place)
                    <tr>
                        <td class="text-muted">Lieu de naissance:</td>
                        <td><strong>{{ $student->birth_place }}</strong></td>
                    </tr>
                    @endif
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
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Attention</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        La modification de la classe ou de l'année scolaire peut affecter les données académiques de l'élève.
                    </small>
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
