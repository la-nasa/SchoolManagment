@extends('layouts.app')

@section('title', 'Modifier la Classe')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Modifier la Classe</h3>
            <p class="mt-1 text-sm text-gray-500">Mettez à jour les informations de la classe</p>
        </div>

        <form action="{{ route('admin.classes.update', $class) }}" method="POST" class="px-4 py-5 sm:p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Class Information -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nom de la classe *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                               class="mt-1 form-input @error('name') border-red-500 @enderror"
                               placeholder="Ex: 6ème A, Terminale S">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Niveau *</label>
                        <select name="level" id="level" required
                                class="mt-1 form-select @error('level') border-red-500 @enderror">
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
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="academic_year" class="block text-sm font-medium text-gray-700">Année scolaire *</label>
                        <select name="academic_year" id="academic_year" required
                                class="mt-1 form-select @error('academic_year') border-red-500 @enderror">
                            @for($year = date('Y') - 1; $year <= date('Y') + 1; $year++)
                                <option value="{{ $year }}-{{ $year + 1 }}" {{ old('academic_year', $class->academic_year) == $year . '-' . ($year + 1) ? 'selected' : '' }}>
                                    {{ $year }}-{{ $year + 1 }}
                                </option>
                            @endfor
                        </select>
                        @error('academic_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Capacité maximale</label>
                        <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->capacity) }}"
                               class="mt-1 form-input @error('capacity') border-red-500 @enderror"
                               min="10" max="60">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Head Teacher -->
                <div>
                    <label for="head_teacher_id" class="block text-sm font-medium text-gray-700">Enseignant titulaire</label>
                    <select name="head_teacher_id" id="head_teacher_id"
                            class="mt-1 form-select @error('head_teacher_id') border-red-500 @enderror">
                        <option value="">Aucun enseignant titulaire</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('head_teacher_id', $class->head_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->matricule }})
                            </option>
                        @endforeach
                    </select>
                    @error('head_teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 form-textarea @error('description') border-red-500 @enderror"
                              placeholder="Description de la classe...">{{ old('description', $class->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Statut</label>
                    <div class="mt-2 flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Classe active</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.classes.index') }}" class="btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn-primary">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
