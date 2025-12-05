@extends('layouts.app')

@section('title', 'Éditer le Trimestre')
@section('page-title', 'Éditer: ' . $term->name)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.academic.school-years') }}">Années Scolaires</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.academic.terms', $term->schoolYear) }}">{{ $term->schoolYear->year }}</a></li>
<li class="breadcrumb-item active">Éditer</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.academic.terms', $term->schoolYear) }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-pencil me-2"></i>Éditer le Trimestre</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.academic.terms.update', $term) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nom du trimestre *</label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $term->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="order" class="form-label">Ordre *</label>
                            <select name="order" id="order" required
                                class="form-select @error('order') is-invalid @enderror">
                                <option value="1" {{ old('order', $term->order) == 1 ? 'selected' : '' }}>1er Trimestre</option>
                                <option value="2" {{ old('order', $term->order) == 2 ? 'selected' : '' }}>2e Trimestre</option>
                                <option value="3" {{ old('order', $term->order) == 3 ? 'selected' : '' }}>3e Trimestre</option>
                            </select>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Date de début *</label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', $term->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Date de fin *</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date', $term->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_current" id="is_current" value="1"
                                    class="form-check-input"
                                    {{ old('is_current', $term->is_current) ? 'checked' : '' }}>
                                <label for="is_current" class="form-check-label">
                                    Définir comme trimestre actuel
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.academic.terms', $term->schoolYear) }}" class="btn btn-outline-secondary">
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
</div>
@endsection
