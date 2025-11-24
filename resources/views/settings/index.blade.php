@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Paramètres du Système</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <nav class="space-y-1">
                <a href="#general" class="settings-nav-item active" data-tab="general">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Général
                </a>
                <a href="#academic" class="settings-nav-item" data-tab="academic">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l9-5-9-5-9 5 9 5zm0 0v6"/>
                    </svg>
                    Académique
                </a>
                <a href="#grading" class="settings-nav-item" data-tab="grading">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Système de Notation
                </a>
                <a href="#appearance" class="settings-nav-item" data-tab="appearance">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Apparence
                </a>
                <a href="#security" class="settings-nav-item" data-tab="security">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Sécurité
                </a>
            </nav>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3">
            <!-- General Settings -->
            <div id="general-tab" class="settings-tab active">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Paramètres Généraux</h3>
                        <p class="mt-1 text-sm text-gray-500">Informations générales de l'établissement</p>
                    </div>
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="px-4 py-5 sm:p-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="school_name" class="block text-sm font-medium text-gray-700">Nom de l'établissement *</label>
                                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $settings['school_name'] ?? '') }}" required
                                           class="mt-1 form-input">
                                    @error('school_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="school_acronym" class="block text-sm font-medium text-gray-700">Acronyme</label>
                                    <input type="text" name="school_acronym" id="school_acronym" value="{{ old('school_acronym', $settings['school_acronym'] ?? '') }}"  
                                           class="mt-1 form-input">
                                    @error('school_acronym')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="school_address" class="block text-sm font-medium text-gray-700">Adresse</label>
                                <textarea name="school_address" id="school_address" rows="3" class="mt-1 form-textarea">{{ old('school_address', $settings['school_address'] ?? '') }}</textarea>
                                @error('school_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <label for="school_phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                                    <input type="text" name="school_phone" id="school_phone" value="{{ old('school_phone', $settings['school_phone'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('school_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="school_email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="school_email" id="school_email" value="{{ old('school_email', $settings['school_email'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('school_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="school_website" class="block text-sm font-medium text-gray-700">Site web</label>
                                    <input type="url" name="school_website" id="school_website" value="{{ old('school_website', $settings['school_website'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('school_website')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="principal_name" class="block text-sm font-medium text-gray-700">Nom du Directeur</label>
                                    <input type="text" name="principal_name" id="principal_name" value="{{ old('principal_name', $settings['principal_name'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('principal_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="principal_title" class="block text-sm font-medium text-gray-700">Titre du Directeur</label>
                                    <input type="text" name="principal_title" id="principal_title" value="{{ old('principal_title', $settings['principal_title'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('principal_title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Academic Settings -->
            <div id="academic-tab" class="settings-tab hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Paramètres Académiques</h3>
                        <p class="mt-1 text-sm text-gray-500">Configuration de l'année scolaire et des périodes</p>
                    </div>
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="px-4 py-5 sm:p-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="current_academic_year" class="block text-sm font-medium text-gray-700">Année Scolaire Actuelle *</label>
                                    <select name="current_academic_year" id="current_academic_year" required class="mt-1 form-select">
                                        @for($year = date('Y') - 1; $year <= date('Y') + 1; $year++)
                                            <option value="{{ $year }}-{{ $year + 1 }}" {{ old('current_academic_year', $settings['current_academic_year'] ?? '') == $year . '-' . ($year + 1) ? 'selected' : '' }}>
                                                {{ $year }}-{{ $year + 1 }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('current_academic_year')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="current_trimester" class="block text-sm font-medium text-gray-700">Trimestre Actuel *</label>
                                    <select name="current_trimester" id="current_trimester" required class="mt-1 form-select">
                                        <option value="1" {{ old('current_trimester', $settings['current_trimester'] ?? '') == 1 ? 'selected' : '' }}>Premier Trimestre</option>
                                        <option value="2" {{ old('current_trimester', $settings['current_trimester'] ?? '') == 2 ? 'selected' : '' }}>Deuxième Trimestre</option>
                                        <option value="3" {{ old('current_trimester', $settings['current_trimester'] ?? '') == 3 ? 'selected' : '' }}>Troisième Trimestre</option>
                                    </select>
                                    @error('current_trimester')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div>
                                    <label for="trimester1_start" class="block text-sm font-medium text-gray-700">Début Trimestre 1</label>
                                    <input type="date" name="trimester1_start" id="trimester1_start" value="{{ old('trimester1_start', $settings['trimester1_start'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('trimester1_start')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="trimester2_start" class="block text-sm font-medium text-gray-700">Début Trimestre 2</label>
                                    <input type="date" name="trimester2_start" id="trimester2_start" value="{{ old('trimester2_start', $settings['trimester2_start'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('trimester2_start')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="trimester3_start" class="block text-sm font-medium text-gray-700">Début Trimestre 3</label>
                                    <input type="date" name="trimester3_start" id="trimester3_start" value="{{ old('trimester3_start', $settings['trimester3_start'] ?? '') }}"
                                           class="mt-1 form-input">
                                    @error('trimester3_start')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Séquences par Trimestre</label>
                                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="flex items-center">
                                        <input type="radio" id="sequences_2" name="sequences_per_trimester" value="2"
                                               {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 2 ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="sequences_2" class="ml-3 block text-sm font-medium text-gray-700">2 séquences</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="sequences_3" name="sequences_per_trimester" value="3"
                                               {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 3 ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="sequences_3" class="ml-3 block text-sm font-medium text-gray-700">3 séquences</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="sequences_4" name="sequences_per_trimester" value="4"
                                               {{ old('sequences_per_trimester', $settings['sequences_per_trimester'] ?? '') == 4 ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="sequences_4" class="ml-3 block text-sm font-medium text-gray-700">4 séquences</label>
                                    </div>
                                </div>
                                @error('sequences_per_trimester')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grading System -->
            <div id="grading-tab" class="settings-tab hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Système de Notation</h3>
                        <p class="mt-1 text-sm text-gray-500">Configuration des barèmes et coefficients</p>
                    </div>
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="px-4 py-5 sm:p-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barème de Notation</label>
                                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="flex items-center">
                                        <input type="radio" id="grading_20" name="grading_system" value="20"
                                               {{ old('grading_system', $settings['grading_system'] ?? '') == 20 ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="grading_20" class="ml-3 block text-sm font-medium text-gray-700">Barème sur 20</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="grading_100" name="grading_system" value="100"
                                               {{ old('grading_system', $settings['grading_system'] ?? '') == 100 ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="grading_100" class="ml-3 block text-sm font-medium text-gray-700">Barème sur 100</label>
                                    </div>
                                </div>
                                @error('grading_system')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="passing_mark" class="block text-sm font-medium text-gray-700">Note de Passage *</label>
                                    <input type="number" name="passing_mark" id="passing_mark" value="{{ old('passing_mark', $settings['passing_mark'] ?? '') }}" required
                                           class="mt-1 form-input" min="0" max="{{ old('grading_system', $settings['grading_system'] ?? 20) }}" step="0.5">
                                    <p class="mt-1 text-sm text-gray-500">Note minimale pour valider</p>
                                    @error('passing_mark')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="excellent_mark" class="block text-sm font-medium text-gray-700">Seuil d'Excellence</label>
                                    <input type="number" name="excellent_mark" id="excellent_mark" value="{{ old('excellent_mark', $settings['excellent_mark'] ?? '') }}"
                                           class="mt-1 form-input" min="0" max="{{ old('grading_system', $settings['grading_system'] ?? 20) }}" step="0.5">
                                    <p class="mt-1 text-sm text-gray-500">Note pour considérer excellent</p>
                                    @error('excellent_mark')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Méthode de Calcul</label>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-center">
                                        <input type="radio" id="calculation_average" name="calculation_method" value="average"
                                               {{ old('calculation_method', $settings['calculation_method'] ?? '') == 'average' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="calculation_average" class="ml-3 block text-sm font-medium text-gray-700">Moyenne simple</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="calculation_weighted" name="calculation_method" value="weighted"
                                               {{ old('calculation_method', $settings['calculation_method'] ?? '') == 'weighted' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="calculation_weighted" class="ml-3 block text-sm font-medium text-gray-700">Moyenne pondérée</label>
                                    </div>
                                </div>
                                @error('calculation_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Arrondi des Notes</label>
                                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="flex items-center">
                                        <input type="radio" id="rounding_none" name="rounding_method" value="none"
                                               {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'none' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="rounding_none" class="ml-3 block text-sm font-medium text-gray-700">Aucun</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="rounding_half" name="rounding_method" value="half"
                                               {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'half' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="rounding_half" class="ml-3 block text-sm font-medium text-gray-700">Demi-point</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="rounding_whole" name="rounding_method" value="whole"
                                               {{ old('rounding_method', $settings['rounding_method'] ?? '') == 'whole' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="rounding_whole" class="ml-3 block text-sm font-medium text-gray-700">Point entier</label>
                                    </div>
                                </div>
                                @error('rounding_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Appearance Settings -->
            <div id="appearance-tab" class="settings-tab hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Apparence</h3>
                        <p class="mt-1 text-sm text-gray-500">Personnalisation de l'interface</p>
                    </div>
                    <form action="{{ route('admin.settings.appearance') }}" method="POST" enctype="multipart/form-data" class="px-4 py-5 sm:p-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="primary_color" class="block text-sm font-medium text-gray-700">Couleur Primaire</label>
                                    <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#1e40af') }}"
                                           class="mt-1 h-10 w-full rounded border-gray-300">
                                    @error('primary_color')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="secondary_color" class="block text-sm font-medium text-gray-700">Couleur Secondaire</label>
                                    <input type="color" name="secondary_color" id="secondary_color" value="{{ old('secondary_color', $settings['secondary_color'] ?? '#f59e0b') }}"
                                           class="mt-1 h-10 w-full rounded border-gray-300">
                                    @error('secondary_color')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Thème</label>
                                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="flex items-center">
                                        <input type="radio" id="theme_light" name="theme" value="light"
                                               {{ old('theme', $settings['theme'] ?? '') == 'light' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="theme_light" class="ml-3 block text-sm font-medium text-gray-700">Thème Clair</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" id="theme_dark" name="theme" value="dark"
                                               {{ old('theme', $settings['theme'] ?? '') == 'dark' ? 'checked' : '' }} class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                        <label for="theme_dark" class="ml-3 block text-sm font-medium text-gray-700">Thème Sombre</label>
                                    </div>
                                </div>
                                @error('theme')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700">Logo de l'établissement</label>
                                <div class="mt-2 flex items-center">
                                    <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                        @if(isset($settings['logo']) && $settings['logo'])
                                            <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo" class="h-16 w-16 rounded">
                                        @else
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <input type="file" name="logo" id="logo" accept="image/*" class="form-input">
                                        <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                                    </div>
                                </div>
                                @error('logo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div id="security-tab" class="settings-tab hidden">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Sécurité</h3>
                        <p class="mt-1 text-sm text-gray-500">Paramètres de sécurité et de session</p>
                    </div>
                    <form action="{{ route('admin.settings.updatesecurity') }}" method="POST" class="px-4 py-5 sm:p-6">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="session_timeout" class="block text-sm font-medium text-gray-700">Délai d'expiration de session (minutes)</label>
                                    <input type="number" name="session_timeout" id="session_timeout" value="{{ old('session_timeout', $settings['session_timeout'] ?? 120) }}"
                                           class="mt-1 form-input" min="15" max="1440">
                                    @error('session_timeout')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="max_login_attempts" class="block text-sm font-medium text-gray-700">Tentatives de connexion max</label>
                                    <input type="number" name="max_login_attempts" id="max_login_attempts" value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? 5) }}"
                                           class="mt-1 form-input" min="1" max="10">
                                    @error('max_login_attempts')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Politique de mot de passe</label>
                                <div class="mt-2 space-y-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="password_uppercase" id="password_uppercase" value="1"
                                               {{ old('password_uppercase', $settings['password_uppercase'] ?? '') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <label for="password_uppercase" class="ml-2 text-sm text-gray-700">Au moins une majuscule</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="password_lowercase" id="password_lowercase" value="1"
                                               {{ old('password_lowercase', $settings['password_lowercase'] ?? '') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <label for="password_lowercase" class="ml-2 text-sm text-gray-700">Au moins une minuscule</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="password_numbers" id="password_numbers" value="1"
                                               {{ old('password_numbers', $settings['password_numbers'] ?? '') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <label for="password_numbers" class="ml-2 text-sm text-gray-700">Au moins un chiffre</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="password_symbols" id="password_symbols" value="1"
                                               {{ old('password_symbols', $settings['password_symbols'] ?? '') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <label for="password_symbols" class="ml-2 text-sm text-gray-700">Au moins un symbole</label>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="password_min_length" class="block text-sm font-medium text-gray-700">Longueur minimale</label>
                                    <input type="number" name="password_min_length" id="password_min_length" value="{{ old('password_min_length', $settings['password_min_length'] ?? 8) }}"
                                           class="mt-1 form-input" min="6" max="20">
                                    @error('password_min_length')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_expiry_days" class="block text-sm font-medium text-gray-700">Expiration (jours)</label>
                                    <input type="number" name="password_expiry_days" id="password_expiry_days" value="{{ old('password_expiry_days', $settings['password_expiry_days'] ?? 90) }}"
                                           class="mt-1 form-input" min="30" max="365">
                                    <p class="mt-1 text-sm text-gray-500">0 pour ne jamais expirer</p>
                                    @error('password_expiry_days')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn-primary">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.settings-nav-item {
    @apply flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-900 bg-gray-100;
}

.settings-nav-item:not(.active) {
    @apply text-gray-600 hover:text-gray-900 hover:bg-gray-50;
}

.settings-tab {
    @apply space-y-6;
}

.settings-tab.hidden {
    display: none;
}

.settings-tab.active {
    display: block;
}

.form-input {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-textarea {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-select {
    @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.btn-primary {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500;
}
</style>
@endpush

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
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Add active class to clicked item
            this.classList.add('active');

            // Show corresponding tab
            const tabId = this.getAttribute('data-tab');
            const tab = document.getElementById(tabId + '-tab');
            if (tab) {
                tab.classList.remove('hidden');
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
@endpush