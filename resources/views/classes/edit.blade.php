@extends('layouts.app')

@section('title', 'Modifier la Classe - ' . $class->name)
@section('page-title', 'Modifier la Classe')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.classes.index') }}">Classes</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.classes.show', $class) }}">{{ $class->name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.classes.show', $class) }}" class="btn btn-outline-secondary">
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
                <form method="POST" action="{{ route('admin.classes.update', $class) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom de la Classe *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}"
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
                                <option value="6ème" {{ old('level', $class->level) == '6ème' ? 'selected' : '' }}>6ème</option>
                                <option value="5ème" {{ old('level', $class->level) == '5ème' ? 'selected' : '' }}>5ème</option>
                                <option value="4ème" {{ old('level', $class->level) == '4ème' ? 'selected' : '' }}>4ème</option>
                                <option value="3ème" {{ old('level', $class->level) == '3ème' ? 'selected' : '' }}>3ème</option>
                                <option value="2nde" {{ old('level', $class->level) == '2nde' ? 'selected' : '' }}>2nde</option>
                                <option value="1ère" {{ old('level', $class->level) == '1ère' ? 'selected' : '' }}>1ère</option>
                                <option value="Terminale" {{ old('level', $class->level) == 'Terminale' ? 'selected' : '' }}>Terminale</option>
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="school_year_id" class="form-label">Année scolaire *</label>
                            <select name="school_year_id" id="school_year_id" required
                                class="form-select @error('school_year_id') is-invalid @enderror">
                                @foreach ($schoolYears as $year)
                                    <option value="{{ $year->id }}"
                                        {{ old('school_year_id', $class->school_year_id) == $year->id ? 'selected' : '' }}>
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
                            <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->capacity) }}"
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
                                        {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
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
                                placeholder="Description de la classe...">{{ old('description', $class->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="is_active" class="form-check-label">Classe active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Mettre à jour
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
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations actuelles</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">{{ $class->level }}</span>
                    </div>
                    <h6>{{ $class->name }}</h6>
                    <p class="text-muted mb-1">{{ $class->schoolYear->year ?? 'N/A' }}</p>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Élèves:</td>
                        <td><strong>{{ $class->students_count ?? 0 }}/{{ $class->capacity }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Titulaire:</td>
                        <td><strong>{{ $class->teacher->name ?? 'Non assigné' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut:</td>
                        <td>
                            @if($class->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Créée le:</td>
                        <td><strong>{{ $class->created_at->format('d/m/Y') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
