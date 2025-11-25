@extends('layouts.app')

@section('title', 'Créer une Matière')
@section('page-title', 'Créer une Matière')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Matières</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Créer une Nouvelle Matière</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.subjects.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nom de la matière *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ex: Mathématiques" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code" class="form-label">Code *</label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                class="form-control @error('code') is-invalid @enderror text-uppercase"
                                placeholder="Ex: MATH" maxlength="10">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="coefficient" class="form-label">Coefficient *</label>
                            <input type="number" name="coefficient" id="coefficient" value="{{ old('coefficient', 1) }}" required
                                class="form-control @error('coefficient') is-invalid @enderror"
                                min="1" max="10" step="0.5">
                            @error('coefficient')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="max_mark" class="form-label">Note Maximale *</label>
                            <input type="number" name="max_mark" id="max_mark" value="{{ old('max_mark', 20) }}" required
                                class="form-control @error('max_mark') is-invalid @enderror"
                                min="10" max="100" step="1">
                            @error('max_mark')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Description de la matière...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="is_active" class="form-check-label">Matière active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Créer la Matière
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
                        <li>Le code doit être unique et en majuscules</li>
                        <li>Le coefficient influence le poids de la matière dans les moyennes</li>
                        <li>La note maximale définit l'échelle de notation</li>
                        <li>Les champs marqués d'un * sont obligatoires</li>
                    </ul>
                </div>

                <div class="mt-3">
                    <h6>Exemples de coefficients:</h6>
                    <ul class="list-unstyled small">
                        <li><strong>1-2:</strong> Matières secondaires</li>
                        <li><strong>3-5:</strong> Matières principales</li>
                        <li><strong>6+:</strong> Matières fondamentales</li>
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
    // Auto-generate code from name
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');

    nameInput.addEventListener('blur', function() {
        if (!codeInput.value && this.value) {
            const name = this.value;
            const code = name.substring(0, 4).toUpperCase().replace(/\s/g, '');
            codeInput.value = code;
        }
    });

    // Force uppercase for code input
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
