@extends('layouts.app')

@section('title', 'Modifier l\'Enseignant - ' . $teacher->name)
@section('page-title', 'Modifier l\'Enseignant')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Enseignants</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.users.show', $teacher) }}">{{ $teacher->name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.users.show', $teacher) }}" class="btn btn-outline-secondary">
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
                <form method="POST" action="{{ route('admin.users.update', $teacher) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $teacher->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $teacher->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $teacher->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date', $teacher->birth_date ? $teacher->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label">Genre</label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                <option value="">Sélectionnez...</option>
                                <option value="M" {{ old('gender', $teacher->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('gender', $teacher->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Rôle *</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Sélectionnez un rôle</option>
                                @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $teacher->roles->first()->name) == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12" id="class_id_field" style="{{ $teacher->class_id || old('role') == 'enseignant titulaire' ? '' : 'display: none;' }}">
                            <label for="class_id" class="form-label">Classe titulaire</label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                <option value="">Sélectionnez une classe</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $teacher->class_id) == $class->id ? 'selected' : '' }}>{{ $class->full_name }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="subjects" class="form-label">Matières enseignées *</label>
                            <select class="form-select @error('subjects') is-invalid @enderror" id="subjects" name="subjects[]" multiple required>
                                @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ in_array($subject->id, old('subjects', $teacher->teacherAssignments->pluck('subject_id')->toArray())) ? 'selected' : '' }}>{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                            @error('subjects')<div class="invalid-feedback">{{ $message }}</div>@enderror>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $teacher->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="photo" class="form-label">Photo</label>
                            @if($teacher->photo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $teacher->photo) }}" alt="Photo actuelle" class="img-thumbnail" width="100">
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
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $teacher->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Enseignant actif</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>Mettre à jour
                        </button>
                        <a href="{{ route('admin.users.show', $teacher) }}" class="btn btn-outline-secondary">Annuler</a>
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
                    <img src="{{ $teacher->photo_url }}" alt="{{ $teacher->name }}" class="rounded-circle" width="80" height="80">
                    <h6 class="mt-2">{{ $teacher->name }}</h6>
                    <p class="text-muted mb-1">{{ $teacher->matricule }}</p>
                    <span class="badge {{ $teacher->isTitularTeacher() ? 'bg-success' : 'bg-info' }}">
                        {{ $teacher->role_name }}
                    </span>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td><strong>{{ $teacher->email }}</strong></td>
                    </tr>
                    @if($teacher->phone)
                    <tr>
                        <td class="text-muted">Téléphone:</td>
                        <td><strong>{{ $teacher->phone }}</strong></td>
                    </tr>
                    @endif
                    @if($teacher->birth_date)
                    <tr>
                        <td class="text-muted">Date de naissance:</td>
                        <td><strong>{{ $teacher->birth_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Âge:</td>
                        <td><strong>{{ $teacher->birth_date->age }} ans</strong></td>
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
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-journal-text me-2"></i>Matières actuelles</h6>
            </div>
            <div class="card-body">
                @if($teacher->teacherAssignments->count() > 0)
                <ul class="list-unstyled mb-0">
                    @foreach($teacher->teacherAssignments->groupBy('class_id') as $classAssignments)
                    @php $class = $classAssignments->first()->class; @endphp
                    <li class="mb-2">
                        <strong>{{ $class->full_name }}:</strong>
                        <br>
                        @foreach($classAssignments as $assignment)
                        <span class="badge bg-light text-dark me-1">{{ $assignment->subject->name }}</span>
                        @endforeach
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-muted mb-0">Aucune matière assignée.</p>
                @endif
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

    // Initialize Select2
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
