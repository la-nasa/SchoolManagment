@extends('layouts.app')

@section('title', 'Paramètres du Système')
@section('page-title', 'Paramètres du Système')

@section('breadcrumbs')
<li class="breadcrumb-item active">Paramètres</li>
@endsection

@section('content')
<div class="row">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3 mb-4">
        <div class="card">
            <div class="card-body p-0">
                <nav class="nav flex-column">
                    <a href="#general" class="nav-link settings-nav-item active" data-tab="general">
                        <i class="bi bi-gear me-2"></i>Général
                    </a>
                    <a href="#academic" class="nav-link settings-nav-item" data-tab="academic">
                        <i class="bi bi-book me-2"></i>Académique
                    </a>
                    <a href="#grading" class="nav-link settings-nav-item" data-tab="grading">
                        <i class="bi bi-graph-up me-2"></i>Système de Notation
                    </a>
                    <a href="#appearance" class="nav-link settings-nav-item" data-tab="appearance">
                        <i class="bi bi-palette me-2"></i>Apparence
                    </a>
                    <a href="#security" class="nav-link settings-nav-item" data-tab="security">
                        <i class="bi bi-shield-lock me-2"></i>Sécurité
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="col-lg-9">
        <!-- General Settings -->
        <div id="general-tab" class="settings-tab active">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Paramètres Généraux</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="school_name" class="form-label">Nom de l'établissement *</label>
                                <input type="text" name="school_name" id="school_name"
                                       value="{{ old('school_name', $settings['school_name'] ?? '') }}"
                                       class="form-control @error('school_name') is-invalid @enderror" required>
                                @error('school_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="school_acronym" class="form-label">Acronyme</label>
                                <input type="text" name="school_acronym" id="school_acronym"
                                       value="{{ old('school_acronym', $settings['school_acronym'] ?? '') }}"
                                       class="form-control @error('school_acronym') is-invalid @enderror">
                                @error('school_acronym')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="school_address" class="form-label">Adresse</label>
                                <textarea name="school_address" id="school_address" rows="3"
                                          class="form-control @error('school_address') is-invalid @enderror">{{ old('school_address', $settings['school_address'] ?? '') }}</textarea>
                                @error('school_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="school_phone" class="form-label">Téléphone</label>
                                <input type="text" name="school_phone" id="school_phone"
                                       value="{{ old('school_phone', $settings['school_phone'] ?? '') }}"
                                       class="form-control @error('school_phone') is-invalid @enderror">
                                @error('school_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="school_email" class="form-label">Email</label>
                                <input type="email" name="school_email" id="school_email"
                                       value="{{ old('school_email', $settings['school_email'] ?? '') }}"
                                       class="form-control @error('school_email') is-invalid @enderror">
                                @error('school_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="school_website" class="form-label">Site web</label>
                                <input type="url" name="school_website" id="school_website"
                                       value="{{ old('school_website', $settings['school_website'] ?? '') }}"
                                       class="form-control @error('school_website') is-invalid @enderror">
                                @error('school_website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="principal_name" class="form-label">Nom du Directeur</label>
                                <input type="text" name="principal_name" id="principal_name"
                                       value="{{ old('principal_name', $settings['principal_name'] ?? '') }}"
                                       class="form-control @error('principal_name') is-invalid @enderror">
                                @error('principal_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="principal_title" class="form-label">Titre du Directeur</label>
                                <input type="text" name="principal_title" id="principal_title"
                                       value="{{ old('principal_title', $settings['principal_title'] ?? '') }}"
                                       class="form-control @error('principal_title') is-invalid @enderror">
                                @error('principal_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Academic Settings -->
        <div id="academic-tab" class="settings-tab d-none">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>Paramètres Académiques</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="current_school_year" class="form-label">Année Scolaire Actuelle *</label>
                                <select name="current_school_year" id="current_school_year" required
                                        class="form-select @error('current_school_year') is-invalid @enderror">
                                    <option value="">Sélectionnez une année</option>
                                    @foreach($schoolYears as $year)
                                        <option value="{{ $year->id }}"
                                            {{ old('current_school_year', $currentSchoolYear->id ?? '') == $year->id ? 'selected' : '' }}>
                                            {{ $year->year }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('current_school_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="current_term" class="form-label">Trimestre Actuel *</label>
                                <select name="current_term" id="current_term" required
                                        class="form-select @error('current_term') is-invalid @enderror">
                                    <option value="">Sélectionnez un trimestre</option>
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}"
                                            {{ old('current_term', $currentTerm->id ?? '') == $term->id ? 'selected' : '' }}>
                                            {{ $term->name }} ({{ $term->schoolYear->year ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('current_term')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="trimester1_start" class="form-label">Début Trimestre 1</label>
                                <input type="date" name="trimester1_start" id="trimester1_start"
                                       value="{{ old('trimester1_start', $settings['trimester1_start'] ?? '') }}"
                                       class="form-control @error('trimester1_start') is-invalid @enderror">
                                @error('trimester1_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="trimester2_start" class="form-label">Début Trimestre 2</label>
                                <input type="date" name="trimester2_start" id="trimester2_start"
                                       value="{{ old('trimester2_start', $settings['trimester2_start'] ?? '') }}"
                                       class="form-control @error('trimester2_start') is-invalid @enderror">
                                @error('trimester2_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="trimester3_start" class="form-label">Début Trimestre 3</label>
                                <input type="date" name="trimester3_start" id="trimester3_start"
                                       value="{{ old('trimester3_start', $settings['trimester3_start'] ?? '') }}"
                                       class="form-control @error('trimester3_start') is-invalid @enderror">
                                @error('trimester3_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Séquences par Trimestre</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="sequences_2" name="sequences_per_trimester" value="2"
                                                   {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 2 ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="sequences_2" class="form-check-label">2 séquences</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="sequences_3" name="sequences_per_trimester" value="3"
                                                   {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 3 ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="sequences_3" class="form-check-label">3 séquences</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="sequences_4" name="sequences_per_trimester" value="4"
                                                   {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 4 ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="sequences_4" class="form-check-label">4 séquences</label>
                                        </div>
                                    </div>
                                </div>
                                @error('sequences_per_trimester')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Grading System -->
        <div id="grading-tab" class="settings-tab d-none">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Système de Notation</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Barème de Notation</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="grading_20" name="grading_system" value="20"
                                                   {{ old('grading_system', $settings['grading_system'] ?? '') == 20 ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="grading_20" class="form-check-label">Barème sur 20</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="grading_100" name="grading_system" value="100"
                                                   {{ old('grading_system', $settings['grading_system'] ?? '') == 100 ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="grading_100" class="form-check-label">Barème sur 100</label>
                                        </div>
                                    </div>
                                </div>
                                @error('grading_system')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="passing_mark" class="form-label">Note de Passage *</label>
                                <input type="number" name="passing_mark" id="passing_mark"
                                       value="{{ old('passing_mark', $settings['passing_mark'] ?? '') }}" required
                                       class="form-control @error('passing_mark') is-invalid @enderror"
                                       min="0" max="{{ old('grading_system', $settings['grading_system'] ?? 20) }}" step="0.5">
                                <div class="form-text">Note minimale pour valider</div>
                                @error('passing_mark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="excellent_mark" class="form-label">Seuil d'Excellence</label>
                                <input type="number" name="excellent_mark" id="excellent_mark"
                                       value="{{ old('excellent_mark', $settings['excellent_mark'] ?? '') }}"
                                       class="form-control @error('excellent_mark') is-invalid @enderror"
                                       min="0" max="{{ old('grading_system', $settings['grading_system'] ?? 20) }}" step="0.5">
                                <div class="form-text">Note pour considérer excellent</div>
                                @error('excellent_mark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Méthode de Calcul</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="calculation_average" name="calculation_method" value="average"
                                                   {{ old('calculation_method', $settings['calculation_method'] ?? '') == 'average' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="calculation_average" class="form-check-label">Moyenne simple</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="calculation_weighted" name="calculation_method" value="weighted"
                                                   {{ old('calculation_method', $settings['calculation_method'] ?? '') == 'weighted' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="calculation_weighted" class="form-check-label">Moyenne pondérée</label>
                                        </div>
                                    </div>
                                </div>
                                @error('calculation_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Arrondi des Notes</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="rounding_none" name="rounding_method" value="none"
                                                   {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'none' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="rounding_none" class="form-check-label">Aucun</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="rounding_half" name="rounding_method" value="half"
                                                   {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'half' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="rounding_half" class="form-check-label">Demi-point</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="radio" id="rounding_whole" name="rounding_method" value="whole"
                                                   {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'whole' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="rounding_whole" class="form-check-label">Point entier</label>
                                        </div>
                                    </div>
                                </div>
                                @error('rounding_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Appearance Settings -->
        <div id="appearance-tab" class="settings-tab d-none">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-palette me-2"></i>Apparence</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.appearance') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="primary_color" class="form-label">Couleur Primaire</label>
                                <input type="color" name="primary_color" id="primary_color"
                                       value="{{ old('primary_color', $settings['primary_color'] ?? '#1e40af') }}"
                                       class="form-control form-control-color">
                                @error('primary_color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="secondary_color" class="form-label">Couleur Secondaire</label>
                                <input type="color" name="secondary_color" id="secondary_color"
                                       value="{{ old('secondary_color', $settings['secondary_color'] ?? '#f59e0b') }}"
                                       class="form-control form-control-color">
                                @error('secondary_color')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Thème</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="theme_light" name="theme" value="light"
                                                   {{ old('theme', $settings['theme'] ?? '') == 'light' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="theme_light" class="form-check-label">Thème Clair</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" id="theme_dark" name="theme" value="dark"
                                                   {{ old('theme', $settings['theme'] ?? '') == 'dark' ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="theme_dark" class="form-check-label">Thème Sombre</label>
                                        </div>
                                    </div>
                                </div>
                                @error('theme')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="logo" class="form-label">Logo de l'établissement</label>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if(isset($settings['logo']) && $settings['logo'])
                                            <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="img-thumbnail" width="80" height="80">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" name="logo" id="logo" accept="image/*" class="form-control">
                                        <div class="form-text">PNG, JPG, GIF jusqu'à 2MB</div>
                                    </div>
                                </div>
                                @error('logo')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div id="security-tab" class="settings-tab d-none">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Sécurité</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.updatesecurity') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="session_timeout" class="form-label">Délai d'expiration de session (minutes)</label>
                                <input type="number" name="session_timeout" id="session_timeout"
                                       value="{{ old('session_timeout', $settings['session_timeout'] ?? 120) }}"
                                       class="form-control @error('session_timeout') is-invalid @enderror"
                                       min="15" max="1440">
                                @error('session_timeout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="max_login_attempts" class="form-label">Tentatives de connexion max</label>
                                <input type="number" name="max_login_attempts" id="max_login_attempts"
                                       value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? 5) }}"
                                       class="form-control @error('max_login_attempts') is-invalid @enderror"
                                       min="1" max="10">
                                @error('max_login_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Politique de mot de passe</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="password_uppercase" id="password_uppercase" value="1"
                                                   {{ old('password_uppercase', $settings['password_uppercase'] ?? '') ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="password_uppercase" class="form-check-label">Au moins une majuscule</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="password_lowercase" id="password_lowercase" value="1"
                                                   {{ old('password_lowercase', $settings['password_lowercase'] ?? '') ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="password_lowercase" class="form-check-label">Au moins une minuscule</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="password_numbers" id="password_numbers" value="1"
                                                   {{ old('password_numbers', $settings['password_numbers'] ?? '') ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="password_numbers" class="form-check-label">Au moins un chiffre</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="password_symbols" id="password_symbols" value="1"
                                                   {{ old('password_symbols', $settings['password_symbols'] ?? '') ? 'checked' : '' }}
                                                   class="form-check-input">
                                            <label for="password_symbols" class="form-check-label">Au moins un symbole</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="password_min_length" class="form-label">Longueur minimale</label>
                                <input type="number" name="password_min_length" id="password_min_length"
                                       value="{{ old('password_min_length', $settings['password_min_length'] ?? 8) }}"
                                       class="form-control @error('password_min_length') is-invalid @enderror"
                                       min="6" max="20">
                                @error('password_min_length')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password_expiry_days" class="form-label">Expiration (jours)</label>
                                <input type="number" name="password_expiry_days" id="password_expiry_days"
                                       value="{{ old('password_expiry_days', $settings['password_expiry_days'] ?? 90) }}"
                                       class="form-control @error('password_expiry_days') is-invalid @enderror"
                                       min="30" max="365">
                                <div class="form-text">0 pour ne jamais expirer</div>
                                @error('password_expiry_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab navigation
    const navItems = document.querySelectorAll('.settings-nav-item');
    const tabs = document.querySelectorAll('.settings-tab');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all items and tabs
            navItems.forEach(nav => nav.classList.remove('active'));
            tabs.forEach(tab => tab.classList.add('d-none'));

            // Add active class to clicked item
            this.classList.add('active');

            // Show corresponding tab
            const tabId = this.getAttribute('data-tab');
            const tab = document.getElementById(tabId + '-tab');
            if (tab) {
                tab.classList.remove('d-none');
                tab.classList.add('active');
            }
        });
    });

    // Dynamic grading system max values
    function updateGradingMaxValues() {
        const gradingSystem = document.querySelector('input[name="grading_system"]:checked');
        const passingMark = document.getElementById('passing_mark');
        const excellentMark = document.getElementById('excellent_mark');

        if (gradingSystem && passingMark && excellentMark) {
            const max = parseInt(gradingSystem.value);
            passingMark.max = max;
            excellentMark.max = max;
        }
    }

    // Update max values when grading system changes
    document.querySelectorAll('input[name="grading_system"]').forEach(radio => {
        radio.addEventListener('change', updateGradingMaxValues);
    });

    // Initialize max values on page load
    updateGradingMaxValues();
});
</script>

<style>
.settings-nav-item {
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
    text-decoration: none;
    color: #6b7280;
    transition: all 0.15s ease-in-out;
}

.settings-nav-item:hover {
    background-color: #f9fafb;
    color: #374151;
}

.settings-nav-item.active {
    background-color: #e5e7eb;
    color: #111827;
    font-weight: 500;
}

.form-control-color {
    height: 3rem;
    padding: 0.375rem;
}
</style>
@endpush
