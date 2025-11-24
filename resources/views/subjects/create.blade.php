@extends('layouts.app')

@section('title', 'Créer une Matière')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Créer une Nouvelle Matière</h3>
            <p class="mt-1 text-sm text-gray-500">Remplissez les informations pour créer une nouvelle matière</p>
        </div>

        <form action="{{ route('admin.subjects.store') }}" method="POST" class="px-4 py-5 sm:p-6">
            @csrf

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom de la matière *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 form-input @error('name') border-red-500 @enderror"
                           placeholder="Ex: Mathématiques">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                           class="mt-1 form-input @error('code') border-red-500 @enderror uppercase"
                           placeholder="Ex: MATH" maxlength="10">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 form-textarea @error('description') border-red-500 @enderror"
                              placeholder="Description de la matière...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coefficient -->
                <div>
                    <label for="coefficient" class="block text-sm font-medium text-gray-700">Coefficient *</label>
                    <input type="number" name="coefficient" id="coefficient" value="{{ old('coefficient', 1) }}" required
                           class="mt-1 form-input @error('coefficient') border-red-500 @enderror"
                           min="1" max="10" step="0.5">
                    @error('coefficient')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Max Mark -->
                <div>
                    <label for="max_mark" class="block text-sm font-medium text-gray-700">Note Maximale *</label>
                    <input type="number" name="max_mark" id="max_mark" value="{{ old('max_mark', 20) }}" required
                           class="mt-1 form-input @error('max_mark') border-red-500 @enderror"
                           min="10" max="100" step="1">
                    @error('max_mark')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Statut</label>
                    <div class="mt-2 flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Matière active</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.subjects.index') }}" class="btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn-primary">
                    Créer la Matière
                </button>
            </div>
        </form>
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
            if (!codeInput.value) {
                const name = this.value;
                const code = name.substring(0, 4).toUpperCase().replace(/\s/g, '');
                codeInput.value = code;
            }
        });
    });
</script>
@endpush
