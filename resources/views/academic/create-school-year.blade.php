@extends('layouts.app')

@section('title', 'Créer une Année Scolaire')
@section('page-title', 'Nouvelle Année Scolaire')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('admin.academic.school-years') }}">Années Scolaires</a></li>
<li class="breadcrumb-item active">Créer</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.academic.school-years') }}" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Retour
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nouvelle Année Scolaire</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.academic.school-years.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="year" class="form-label">Année scolaire *</label>
                            <input type="text" name="year" id="year"
                                class="form-control @error('year') is-invalid @enderror"
                                placeholder="Ex: 2024-2025" value="{{ old('year') }}" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Date de début *</label>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Date de fin *</label>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_current" id="is_current" value="1"
                                    class="form-check-input"
                                    {{ old('is_current') ? 'checked' : '' }}>
                                <label for="is_current" class="form-check-label">
                                    Définir comme année scolaire actuelle
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.academic.school-years') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Créer
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
