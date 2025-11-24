@extends('layouts.app')

@section('title', 'Nouvel Enseignant')
@section('page-title', 'Nouvel Enseignant')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Enseignants</a></li>
<li class="breadcrumb-item active">Nouvel enseignant</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-plus me-2"></i>Informations de l'enseignant</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label">Genre</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Sélectionnez...</option>
                                <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Rôle *</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Sélectionnez un rôle</option>
                                @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12" id="class_id_field" style="display: none;">
                            <label for="class_id" class="form-label">Classe titulaire *</label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->full_name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="subjects" class="form-label">Matières enseignées *</label>
                            <select class="form-select @error('subjects') is-invalid @enderror" id="subjects" name="subjects[]" multiple required>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subjects', [])) ? 'selected' : '' }}>{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                            @error('subjects')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs matières</div>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <i class="bi bi-check-circle me-1"></i>Créer l'enseignant
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
                        <li>Un mot de passe temporaire sera créé</li>
                        <li>L'enseignant devra changer son mot de passe à la première connexion</li>
                        <li>Les champs marqués d'un * sont obligatoires</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6>Rôles disponibles:</h6>
                    <ul class="list-unstyled">
                        <li><span class="badge bg-success">Enseignant titulaire</span> - Gère une classe complète</li>
                        <li><span class="badge bg-info">Enseignant</span> - Enseigne des matières spécifiques</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const classField = document.getElementById('class_id_field');
    const classSelect = document.getElementById('class_id');

    function toggleClassField() {
        if (roleSelect.value === 'enseignant titulaire') {
            classField.style.display = 'block';
            classSelect.required = true;
        } else {
            classField.style.display = 'none';
            classSelect.required = false;
            classSelect.value = '';
        }
    }

    roleSelect.addEventListener('change', toggleClassField);
    toggleClassField(); // Initial state

    // Initialize Select2 for subjects
    $('#subjects').select2({
        placeholder: 'Sélectionnez les matières enseignées',
        allowClear: true
    });

    $('#class_id').select2({
        placeholder: 'Sélectionnez une classe',
        allowClear: false
    });
});
</script>
@endpush
