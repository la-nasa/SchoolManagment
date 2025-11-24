@extends('layouts.app')

@section('title', 'Éditer l\'Année Scolaire')

@section('page-title', 'Éditer: ' . $schoolYear->year)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.academic.school-years.update', $schoolYear) }}" method="POST"
                class="px-4 py-5 sm:p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Année scolaire *</label>
                        <input type="text" name="year" id="year"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('year') border-red-500 @enderror"
                            placeholder="Ex: 2024-2025" value="{{ old('year', $schoolYear->year) }}" required>
                        @error('year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Date de début *</label>
                            <input type="date" name="start_date" id="start_date"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror"
                                value="{{ old('start_date', $schoolYear->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">Date de fin *</label>
                            <input type="date" name="end_date" id="end_date"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror"
                                value="{{ old('end_date', $schoolYear->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_current" id="is_current" value="1"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                            {{ old('is_current', $schoolYear->is_current) ? 'checked' : '' }}>
                        <label for="is_current" class="ml-2 block text-sm text-gray-700">
                            Définir comme année scolaire actuelle
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.academic.school-years') }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
@endsection
