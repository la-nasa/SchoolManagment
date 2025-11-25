@extends('layouts.app')

@section('title', 'Générer les Bulletins')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Génération des Bulletins</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.bulletins.class', $classe) }}" method="GET">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="term_id" class="form-label">Trimestre *</label>
                                    <select name="term_id" id="term_id" class="form-select" required>
                                        <option value="">-- Sélectionnez un trimestre --</option>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}"
                                                {{ $term->id == Term::current()->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="school_year_id" class="form-label">Année Scolaire *</label>
                                    <select name="school_year_id" id="school_year_id" class="form-select" required>
                                        <option value="">-- Sélectionnez une année --</option>
                                        @foreach ($schoolYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ $year->id == SchoolYear::current()->id ? 'selected' : '' }}>
                                                {{ $year->year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label">Type de Bulletin *</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="type" id="type_standard"
                                            value="standard" checked>
                                        <label class="btn btn-outline-primary" for="type_standard">
                                            <i class="bi bi-file-text me-2"></i>Bulletin Standard
                                        </label>

                                        <input type="radio" class="btn-check" name="type" id="type_apc"
                                            value="apc">
                                        <label class="btn btn-outline-info" for="type_apc">
                                            <i class="bi bi-file-earmark me-2"></i>Bulletin APC
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Les bulletins de {{ $classe->students()->count() }} élèves seront générés dans un fichier
                                ZIP.
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download me-2"></i>Générer les Bulletins
                                </button>
                                <a href="{{ route('admin.classes.show', $classe) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PV Section -->
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Générer les Procès-Verbaux</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.bulletins.class-pv', $classe) }}" method="GET">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="pv_term_id" class="form-label">Trimestre *</label>
                                    <select name="term_id" id="pv_term_id" class="form-select" required>
                                        <option value="">-- Sélectionnez un trimestre --</option>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}"
                                                {{ $term->id == Term::current()->id ? 'selected' : '' }}>
                                                {{ $term->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="pv_school_year_id" class="form-label">Année Scolaire *</label>
                                    <select name="school_year_id" id="pv_school_year_id" class="form-select" required>
                                        <option value="">-- Sélectionnez une année --</option>
                                        @foreach ($schoolYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ $year->id == SchoolYear::current()->id ? 'selected' : '' }}>
                                                {{ $year->year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Les PV de toutes les évaluations seront générés dans un fichier ZIP.
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-download me-2"></i>Générer les PV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
