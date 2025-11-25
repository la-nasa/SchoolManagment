@extends('layouts.app')

@section('title', 'Modifier la Matière - ' . $subject->name)
@section('page-title', 'Modifier la Matière')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.subjects.index') }}">Matières</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.subjects.show', $subject) }}">{{ $subject->name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline-secondary">
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
                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nom de la matière *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $subject->name) }}"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ex: Mathématiques" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code" class="form-label">Code *</label>
                            <input type="text" name="code" id="code" value="{{ old('code', $subject->code) }}" required
                                class="form-control @error('code') is-invalid @enderror text-uppercase"
                                placeholder="Ex: MATH" maxlength="10">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="coefficient" class="form-label">Coefficient *</label>
                            <input type="number" name="coefficient" id="coefficient" value="{{ old('coefficient', $subject->coefficient) }}" required
                                class="form-control @error('coefficient') is-invalid @enderror"
                                min="1" max="10" step="0.5">
                            @error('coefficient')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="max_mark" class="form-label">Note Maximale *</label>
                            <input type="number" name="max_mark" id="max_mark" value="{{ old('max_mark', $subject->max_mark) }}" required
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
                                placeholder="Description de la matière...">{{ old('description', $subject->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $subject->is_active) ? 'checked' : '' }}
                                    class="form-check-input">
                                <label for="is_active" class="form-check-label">Matière active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline-secondary">
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
                        <span class="text-white fw-bold">{{ substr($subject->name, 0, 1) }}</span>
                    </div>
                    <h6>{{ $subject->name }}</h6>
                    <p class="text-muted mb-1">{{ $subject->code }}</p>
                </div>

                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Coefficient:</td>
                        <td><strong>{{ $subject->coefficient }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Note max:</td>
                        <td><strong>{{ $subject->max_mark }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Enseignants:</td>
                        <td><strong>{{ $subject->teachers_count ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut:</td>
                        <td>
                            @if($subject->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Créée le:</td>
                        <td><strong>{{ $subject->created_at->format('d/m/Y') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Force uppercase for code input
    const codeInput = document.getElementById('code');
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
